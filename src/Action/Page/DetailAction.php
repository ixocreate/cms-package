<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Doctrine\Common\Collections\Criteria;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Admin\Container;
use Ixocreate\Cms\Admin\Item;
use Ixocreate\Cms\Config\Config;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Schema\Builder\BuilderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DetailAction implements MiddlewareInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var BuilderInterface
     */
    private $schemaBuilder;

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * DetailAction constructor.
     * @param Config $config
     * @param BuilderInterface $schemaBuilder
     * @param PageVersionRepository $pageVersionRepository
     * @param PageRepository $pageRepository
     * @param Container $container
     */
    public function __construct(
        Config $config,
        BuilderInterface $schemaBuilder,
        PageVersionRepository $pageVersionRepository,
        PageRepository $pageRepository,
        Container $container
    ) {
        $this->config = $config;
        $this->schemaBuilder = $schemaBuilder;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->pageRepository = $pageRepository;
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pageId = $request->getAttribute("id");

        /** @var Page $page */
        $page = $this->pageRepository->find($pageId);
        if (empty($page)) {
            return new ApiErrorResponse("invalid_page_id");
        }

        /** @var Item $item */
        $item = $this->container->find(function (Item $item) use ($page) {
            return $item->structureItem()->sitemapId() === (string) $page->sitemapId();
        });
        if (empty($item)) {
            return new ApiErrorResponse("invalid_page_id");
        }

        $result = $item->jsonSerialize();
        $result['hasChildren'] = (\count($result['children']) > 0);
        unset($result['children'], $result['childrenAllowed']);


        $result['localizedPages'] = [];
        foreach ($result['pages'] as $locale => $pageData) {
            if ($locale === $page->locale()) {
                $result['page'] = $pageData;
                continue;
            }

            $result['localizedPages'][$locale] = $pageData;
        }
        unset($result['pages']);

        $result['page']['version'] = [
            'head' => null,
            'approved' => null,
        ];
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('pageId', $page['page']->id()));
        $criteria->andWhere(Criteria::expr()->neq('approvedAt', null));
        $criteria->orderBy(['approvedAt' => 'DESC']);
        $criteria->setMaxResults(1);
        $pageVersion = $this->pageVersionRepository->matching($criteria);
        if ($pageVersion->count() > 0) {
            /** @var PageVersion $pageVersion */
            $pageVersion = $pageVersion->current();
            $result['page']['version']['approved'] = (string) $pageVersion->id();
        }
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('pageId', $page['page']->id()));
        $criteria->orderBy(['createdAt' => 'DESC']);
        $criteria->setMaxResults(1);
        $pageVersion = $this->pageVersionRepository->matching($criteria);
        if ($pageVersion->count() > 0) {
            /** @var PageVersion $pageVersion */
            $pageVersion = $pageVersion->current();
            $result['page']['version']['head'] = (string) $pageVersion->id();
        }

        $navigation = $this->config->navigation();
        $navigation = \array_map(function ($value) use ($item) {
            $value['active'] = (\in_array($value['name'], $item->navigation()));
            return $value;
        }, $navigation);
        $result['navigation'] = $navigation;

        $result['schema'] = $item->pageType()->provideSchema('', $this->schemaBuilder);

        return new ApiSuccessResponse($result);
    }
}
