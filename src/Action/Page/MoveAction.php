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

class MoveAction implements MiddlewareInterface
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
        if (empty($sitemap)) {
            return new ApiErrorResponse('sitemap_not_found', [], 500);
        }

        if ($data['prevSibling'] !== null) {
            /** @var Sitemap $sibling */
            $sibling = $this->sitemapRepository->find($data['prevSibling']);
            if (empty($sibling)) {
                return new ApiErrorResponse('prevSibling_not_found', [], 500);
            }

            //TODO pageType Check

            $this->sitemapRepository->moveAsNextSibling($sitemap, $sibling);
        } elseif ($data['parent'] !== null) {
            /** @var Sitemap $parent */
            $parent = $this->sitemapRepository->find($data['parent']);
            if (empty($parent)) {
                return new ApiErrorResponse('parent_not_found', [], 500);
            }

            //TODO pageType Check

            $this->sitemapRepository->moveAsFirstChild($sitemap, $parent);
        } else {
            //TODO should us a "moveToFirstRoot"
            $sibling = $this->sitemapRepository->findBy(['nestedLeft' => 1]);
            if (empty($sibling)) {
                return new ApiErrorResponse('root_not_found', [], 500);
            }

            //TODO pageType Check

            $sibling = $sibling[0];
            $this->sitemapRepository->moveAsPreviousSibling($sitemap, $sibling);
        }

        return new ApiSuccessResponse();
    }
}
