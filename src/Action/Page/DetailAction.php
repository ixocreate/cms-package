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
use Ixocreate\Cms\Config\Config;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Tree\AdminTreeFactory;
use Ixocreate\Cms\Tree\MutationCollection;
use Ixocreate\Intl\LocaleManager;
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
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var AdminTreeFactory
     */
    private $adminTreeFactory;
    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * DetailAction constructor.
     * @param Config $config
     * @param BuilderInterface $schemaBuilder
     * @param PageVersionRepository $pageVersionRepository
     * @param PageRepository $pageRepository
     * @param AdminTreeFactory $adminTreeFactory
     * @param LocaleManager $localeManager
     */
    public function __construct(
        Config $config,
        BuilderInterface $schemaBuilder,
        PageVersionRepository $pageVersionRepository,
        PageRepository $pageRepository,
        AdminTreeFactory $adminTreeFactory,
        LocaleManager $localeManager
    ) {
        $this->config = $config;
        $this->schemaBuilder = $schemaBuilder;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->pageRepository = $pageRepository;
        $this->adminTreeFactory = $adminTreeFactory;
        $this->localeManager = $localeManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pageId = $request->getAttribute("id");
        /** @var Page $page */
        $page = $this->pageRepository->find($pageId);
        if (empty($page)) {
            return new ApiErrorResponse("invalid_page_id");
        }

        $item = $this->adminTreeFactory->createItem((string) $page->sitemapId(), new MutationCollection());
        $result = [
            'sitemap' => $item->sitemap()->toPublicArray(),
            'handle' => $item->sitemap()->handle(),
            'pageType' => [
                'label' => $item->pageType()->label(),
                'allowedChildren' => $item->pageType()->allowedChildren(),
                'isRoot' => $item->pageType() instanceof RootPageTypeInterface,
                'name' => $item->pageType()::serviceName(),
                'terminal' => $item->pageType() instanceof TerminalPageTypeInterface
            ],
            'page' => [
                'page' => $page->toPublicArray(),
                //TODO URL
                'url' => '',
                'isOnline' => $item->isOnline($page->locale()),
                'version' => [
                    'head' => null,
                    'approved' => null,
                ]
            ],
            'localizedPages' => [],

        ];

        foreach ($this->localeManager->all() as $locale) {
            $locale = $locale['locale'];

            if ($page->locale() === $locale) {
                continue;
            }

            if (!$item->hasPage($locale)) {
                continue;
            }

            $result['localizedPages'][$locale] = $item->page($locale);
        }

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('pageId', $page->id()));
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
        $criteria->where(Criteria::expr()->eq('pageId', $page->id()));
        $criteria->orderBy(['createdAt' => 'DESC']);
        $criteria->setMaxResults(1);
        $pageVersion = $this->pageVersionRepository->matching($criteria);
        if ($pageVersion->count() > 0) {
            /** @var PageVersion $pageVersion */
            $pageVersion = $pageVersion->current();
            $result['page']['version']['head'] = (string) $pageVersion->id();
        }

        $result['hasChildren'] = (\count($item->below()) > 0);

        $navigation = $this->config->navigation();
        $navigation = \array_map(function ($value) use ($item, $page) {
            $value['active'] = (\in_array($value['name'], $item->navigation($page->locale())));
            return $value;
        }, $navigation);
        $result['navigation'] = $navigation;

        $result['schema'] = $item->pageType()->provideSchema('', $this->schemaBuilder);

        return new ApiSuccessResponse($result);
    }
}
