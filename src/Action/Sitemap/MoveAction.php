<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Sitemap;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\Repository\SitemapRepository;
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
     * @param PageTypeSubManager $pageTypeSubManager
     */
    public function __construct(SitemapRepository $sitemapRepository, PageTypeSubManager $pageTypeSubManager)
    {
        $this->sitemapRepository = $sitemapRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();

        if (empty($data['id'])) {
            return new ApiErrorResponse('id_invalid', [], 400);
        }

        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($data['id']);
        if (empty($sitemap)) {
            return new ApiErrorResponse('sitemap_not_found', [], 400);
        }

        if ($data['prevSiblingId'] !== null) {
            /** @var Sitemap $sibling */
            $sibling = $this->sitemapRepository->find($data['prevSiblingId']);
            if (empty($sibling)) {
                return new ApiErrorResponse('prevSibling_not_found', [], 400);
            }

            if ($sibling->parentId() === null) {
                $pageType = $this->pageTypeSubManager->get($sitemap->pageType());
                if (!\is_subclass_of($pageType, RootPageTypeInterface::class)) {
                    return new ApiErrorResponse('invalid_prevSiblingId', [], 400);
                }
            } else {
                $parent = $this->sitemapRepository->find($sibling->parentId());
                /** @var PageTypeInterface $parentPageType */
                $parentPageType = $this->pageTypeSubManager->get($parent->pageType());
                if ($parentPageType->allowedChildren() === null || !\in_array($sitemap->pageType(), $parentPageType->allowedChildren())) {
                    return new ApiErrorResponse('invalid_parentId', [], 400);
                }
            }

            $this->sitemapRepository->moveAsNextSibling($sitemap, $sibling);
        } elseif ($data['parentId'] !== null) {
            /** @var Sitemap $parent */
            $parent = $this->sitemapRepository->find($data['parentId']);
            if (empty($parent)) {
                return new ApiErrorResponse('parent_not_found', [], 400);
            }

            /** @var PageTypeInterface $parentPageType */
            $parentPageType = $this->pageTypeSubManager->get($parent->pageType());
            if ($parentPageType->allowedChildren() === null || !\in_array($sitemap->pageType(), $parentPageType->allowedChildren())) {
                return new ApiErrorResponse('invalid_parentId', [], 400);
            }

            $this->sitemapRepository->moveAsFirstChild($sitemap, $parent);
        } else {
            $pageType = $this->pageTypeSubManager->get($sitemap->pageType());
            if (!\is_subclass_of($pageType, RootPageTypeInterface::class)) {
                return new ApiErrorResponse('invalid_target', [], 400);
            }

            //TODO should us a "moveToFirstRoot"
            //$this->sitemapRepository->moveAsFirstRoot($sitemap);

            $sibling = $this->sitemapRepository->findBy(['nestedLeft' => 1]);
            if (empty($sibling)) {
                return new ApiErrorResponse('root_not_found', [], 400);
            }

            $sibling = $sibling[0];
            $this->sitemapRepository->moveAsPreviousSibling($sitemap, $sibling);
        }

        return new ApiSuccessResponse();
    }
}
