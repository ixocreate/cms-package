<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Sitemap;

use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
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

    /**
     * IndexAction constructor.
     * @param Builder $builder
     * @param PageTypeSubManager $pageTypeSubManager
     * @param DatabaseSitemapLoader $databaseSitemapLoader
     */
    public function __construct(
        Builder $builder,
        PageTypeSubManager $pageTypeSubManager,
        DatabaseSitemapLoader $databaseSitemapLoader
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
                return !(\is_subclass_of($item->parent()->pageType(), TerminalPageTypeInterface::class));
            }),
            'allowedAddingRoot' => (\count($this->pageTypeSubManager->allowedPageTypes($this->databaseSitemapLoader->receiveHandles())) > 0),
        ]);
    }
}
