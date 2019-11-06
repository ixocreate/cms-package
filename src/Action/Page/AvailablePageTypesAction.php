<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\SitemapRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AvailablePageTypesAction implements MiddlewareInterface
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    public function __construct(
        PageTypeSubManager $pageTypeSubManager,
        SitemapRepository $sitemapRepository
    ) {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parentSitemapId = $request->getAttribute("parentSitemapId", null);
        $parentPageType = null;
        if (!empty($parentSitemapId)) {
            /** @var Sitemap $sitemap */
            $sitemap = $this->sitemapRepository->find($parentSitemapId);
            if (empty($sitemap)) {
                return new ApiErrorResponse("invalid_parentSitemapId");
            }
            $parentPageType = $sitemap->pageType();
        }

        $result = [];
        $allowedPageTypes = $this->pageTypeSubManager->allowedPageTypes($this->sitemapRepository->receiveUsedHandles(), $parentPageType);
        foreach ($allowedPageTypes as $allowedPageType) {
            /** @var PageTypeInterface $allowedPageType */
            $allowedPageType = $this->pageTypeSubManager->get($allowedPageType);
            $result[] = [
                'name' => $allowedPageType::serviceName(),
                'label' => $allowedPageType->label(),
            ];
        }

        return new ApiSuccessResponse($result);
    }
}
