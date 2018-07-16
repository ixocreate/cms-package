<?php

namespace KiwiSuite\Cms\Action\Page;


use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiListResponse;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Cms\Resource\PageResource;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FlatIndexAction implements MiddlewareInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var PageResource
     */
    private $pageResource;

    public function __construct(SitemapRepository $sitemapRepository, PageRepository $pageRepository, PageResource $pageResource)
    {
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->pageResource = $pageResource;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $sitemap = $this->sitemapRepository->findOneBy(['handle' => $request->getAttribute("handle")]);
        if (empty($sitemap)) {
            return new ApiErrorResponse("invalid_handle");
        }


        return new ApiListResponse($this->pageResource, $this->pageRepository->fetchDirectSiblingsOf($sitemap), ['parent' => $sitemap->toPublicArray()]);
    }
}
