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
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
use Ixocreate\Cms\Repository\NavigationRepository;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Schema\Builder\BuilderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DetailAction implements MiddlewareInterface
{
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
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var PageRoute
     */
    private $pageRoute;
    /**
     * @var NavigationRepository
     */
    private $navigationRepository;

    /**
     * DetailAction constructor.
     * @param BuilderInterface $schemaBuilder
     * @param PageTypeSubManager $pageTypeSubManager
     * @param PageRoute $pageRoute
     * @param PageVersionRepository $pageVersionRepository
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     * @param NavigationRepository $navigationRepository
     */
    public function __construct(
        BuilderInterface $schemaBuilder,
        PageTypeSubManager $pageTypeSubManager,
        PageRoute $pageRoute,
        PageVersionRepository $pageVersionRepository,
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository,
        NavigationRepository $navigationRepository
    ) {
        $this->schemaBuilder = $schemaBuilder;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->pageRoute = $pageRoute;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->navigationRepository = $navigationRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pageId = $request->getAttribute('id');
        /** @var Page $page */
        $page = $this->pageRepository->find($pageId);
        if (empty($page)) {
            return new ApiErrorResponse('invalid_page_id');
        }

        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($page->sitemapId());

        /** @var PageTypeInterface $pageType */
        $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

        $result = [
            'sitemap' => [
                'id' => (string)$sitemap->id(),
                'parentId' => $sitemap->parentId(),
                'nestedLeft' => $sitemap->nestedLeft(),
                'nestedRight' => $sitemap->nestedRight(),
                'pageType' => $sitemap->pageType(),
                'handle' => $sitemap->handle(),
                'level' => $sitemap->level(),
            ],
            'handle' => $sitemap->handle(),
            'pageType' => [
                'label' => $pageType->label(),
                'allowedChildren' => $pageType->allowedChildren(),
                'isRoot' => $pageType instanceof RootPageTypeInterface,
                'name' => $pageType::serviceName(),
                'terminal' => $pageType instanceof TerminalPageTypeInterface,
            ],
        ];

        $pageData = [
            'page' => $page->toArray(),
            'url' => '',
            'isOnline' => $page->isOnline(),
            'version' => [
                'head' => null,
                'approved' => null,
            ],
        ];

        try {
            $pageData['url'] = $this->pageRoute->fromPageId((string)$page->id());
        } catch (\Exception $exception) {
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
            $pageData['version']['approved'] = (string) $pageVersion->id();
        }

        /** @var PageVersion $pageVersion */
        $pageVersion = $this->pageVersionRepository->findOneBy(['pageId' => $page->id()], ['createdAt' => 'DESC']);
        if ($pageVersion !== null) {
            $pageData['version']['head'] = (string)$pageVersion->id();
        }

        $result['page'] = $pageData;

        $localizedPages = [];
        $pageResult = $this->pageRepository->findBy(['sitemapId' => $sitemap->id()]);
        foreach ($pageResult as $pageItem) {
            if ((string)$pageItem->id() === $pageId) {
                continue;
            }
            $localizedPages[$pageItem->locale()] = [
                'page' => $pageItem->toArray(),
                'url' => '',
                'isOnline' => $pageItem->isOnline(),
            ];

            try {
                $localizedPages[$pageItem->locale()]['url'] = $this->pageRoute->fromPageId((string)$pageItem->id());
            } catch (\Exception $exception) {
            }
        }
        $result['localizedPages'] = $localizedPages;

        $result['hasChildren'] = ($sitemap->nestedRight() - $sitemap->nestedLeft() > 1);

        $result['navigation'] = $this->navigationRepository->getNavigationForPage((string)$page->id());

        $result['schema'] = $pageType->provideSchema('', $this->schemaBuilder);

        return new ApiSuccessResponse($result);
    }
}
