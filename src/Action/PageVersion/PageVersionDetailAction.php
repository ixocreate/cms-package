<?php

namespace KiwiSuite\Cms\Action\PageVersion;


use KiwiSuite\Admin\Response\ApiDetailResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Cms\Resource\PageVersionResource;
use KiwiSuite\Schema\Builder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

class PageVersionDetailAction implements MiddlewareInterface
{

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
     * @var Builder
     */
    private $builder;
    /**
     * @var PageVersionResource
     */
    private $pageVersionResource;

    public function __construct(
        PageVersionRepository $pageVersionRepository,
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository,
        PageTypeSubManager $pageTypeSubManager,
        Builder $builder,
        PageVersionResource $pageVersionResource
    )
    {
        $this->pageVersionRepository = $pageVersionRepository;
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->builder = $builder;
        $this->pageVersionResource = $pageVersionResource;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        $result = $this->pageVersionRepository->findBy(['pageId' => $routeResult->getMatchedParams()['id']], ['createdAt' => 'DESC'], 1);

        if (empty($result)) {
            return new ApiSuccessResponse([]);
        }

        /** @var PageVersion $pageVersion */
        $pageVersion = current($result);

        $content = [];
        if (!empty($pageVersion->content()->value())) {
            $content = $pageVersion->content()->value();

        }

        /** @var Page $page */
        $page = $this->pageRepository->find($request->getAttribute("id"));
        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($page->sitemapId());
        /** @var PageTypeInterface $pageType */
        $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

        return new ApiDetailResponse($this->pageVersionResource, $content, $pageType->schema($this->builder), []);
    }
}
