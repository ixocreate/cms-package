<?php

namespace KiwiSuite\Cms\Action\Page;

use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteAction implements MiddlewareInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    public function __construct(PageRepository $pageRepository, PageVersionRepository $pageVersionRepository, SitemapRepository $sitemapRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->sitemapRepository = $sitemapRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute("id");

        /** @var Page $page */
        $page = $this->pageRepository->find($id);
        if (empty($page)) {
            return new ApiErrorResponse("invalid_request");
        }

        $pageVersions = $this->pageVersionRepository->findBy(['pageId' => $page->id()]);

        foreach ($pageVersions as $pageVersion) {
            $this->pageVersionRepository->remove($pageVersion);
        }

        if ($this->pageRepository->count(['sitemapId' => $page->sitemapId()]) === 1) {
            $sitemap = $this->sitemapRepository->find($page->sitemapId());
            //$this->sitemapRepository->remove($sitemap);
        }

        $this->pageRepository->remove($page);

        return new ApiSuccessResponse();
    }
}
