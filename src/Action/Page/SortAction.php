<?php

namespace KiwiSuite\Cms\Action\Page;


use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SortAction implements MiddlewareInterface
{
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * SortAction constructor.
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(SitemapRepository $sitemapRepository, PageTypeSubManager $pageTypeSubManager)
    {
        $this->sitemapRepository = $sitemapRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();

        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($data['id']);


        if ($data['prevSibling'] === null) {

            if ($data['parent'] === null) {

                // move to first position

            } else {
                /** @var Sitemap $parent */
                $parent = $this->sitemapRepository->find($data['parent']);
            }

            /** @var Sitemap $sibling */
            $sibling = $this->sitemapRepository->find($data['id']);
        } else {
            /** @var Sitemap $sibling */
            $sibling = $this->sitemapRepository->find($data['id']);

            /** @var Sitemap $parent */
            $parent = $this->sitemapRepository->find($sibling->parentId());

            /** @var PageTypeInterface $pageType */
            $pageType = $this->pageTypeSubManager->get($parent->pageType());

            if (!empty($pageType->allowedChildren() && !\in_array($sitemap->pageType(), $pageType->allowedChildren()))) {
                return new ApiErrorResponse(501);
            }

            $this->sitemapRepository->moveAsNextSibling($sitemap, $sibling);
        }

        return new ApiSuccessResponse();
    }
}
