<?php

namespace KiwiSuite\Cms\Action\Page;

use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Loader\DatabaseSitemapLoader;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Site\Admin\Builder;
use KiwiSuite\Cms\Site\Admin\Item;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AvailablePageTypesAction implements MiddlewareInterface
{
    /**
     * @var DatabaseSitemapLoader
     */
    private $databaseSitemapLoader;
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var Builder
     */
    private $builder;

    public function __construct(
        DatabaseSitemapLoader $databaseSitemapLoader,
        PageTypeSubManager $pageTypeSubManager,
        Builder $builder
    ) {

        $this->databaseSitemapLoader = $databaseSitemapLoader;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->builder = $builder;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parentSitemapId = $request->getAttribute("parentSitemapId", null);
        $parentPageType = null;

        if (!empty($parentSitemapId)) {
            /** @var Item $item */
            $item = $this->builder->build()->findOneBy(function (Item $item) use ($parentSitemapId) {
                return ((string) $item->sitemap()->id() === $parentSitemapId);
            });

            if (empty($item)) {
                return new ApiErrorResponse("invalid_parentSitemapId");
            }

            $parentPageType = $item->pageType()::serviceName();
        }

        $result = [];
        $allowedPageTypes = $this->pageTypeSubManager->allowedChildPageTypes($this->databaseSitemapLoader->receiveHandles(), $parentPageType);
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
