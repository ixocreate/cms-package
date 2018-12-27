<?php

namespace Ixocreate\Cms\Action\Page;


use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Site\Admin\Builder;
use Ixocreate\Cms\Site\Admin\Item;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexAction implements MiddlewareInterface
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var Builder
     */
    private $builder;
    /**
     * @var DatabaseSitemapLoader
     */
    private $databaseSitemapLoader;

    public function __construct(
        Builder $builder,
        DatabaseSitemapLoader $databaseSitemapLoader,
        PageTypeSubManager $pageTypeSubManager
    ) {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->builder = $builder;
        $this->databaseSitemapLoader = $databaseSitemapLoader;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new ApiSuccessResponse([
            'items' => $this->builder->build()->filter(function (Item $item) {
                if (empty($item->parent())) {
                    return true;
                }
                return !($item->parent()->pageType()->terminal());
            }),
            'allowedAddingRoot' => (count($this->pageTypeSubManager->allowedChildPageTypes($this->databaseSitemapLoader->receiveHandles())) > 0),
        ]);
    }
}
