<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Admin;

use Ixocreate\Cms\Loader\DatabasePageLoader;
use Ixocreate\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\Loader\PageLoaderInterface;
use Ixocreate\Cms\Loader\SitemapLoaderInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Cms\Site\Structure\StructureItem;
use Ixocreate\ServiceManager\SubManager\SubManagerInterface;

final class AdminItemFactory
{
    /**
     * @var PageLoaderInterface
     */
    private $pageLoader;

    /**
     * @var SitemapLoaderInterface
     */
    private $sitemapLoader;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var SubManagerInterface
     */
    private $searchSubManager;

    /**
     * @var PageRoute
     */
    private $pageRoute;

    /**
     * AdminItemFactory constructor.
     * @param DatabasePageLoader $pageLoader
     * @param DatabaseSitemapLoader $sitemapLoader
     * @param SubManagerInterface $pageTypeSubManager
     * @param SubManagerInterface $searchSubManager
     * @param PageRoute $pageRoute
     */
    public function __construct(
        DatabasePageLoader $pageLoader,
        DatabaseSitemapLoader $sitemapLoader,
        SubManagerInterface $pageTypeSubManager,
        SubManagerInterface $searchSubManager,
        PageRoute $pageRoute
    ) {
        $this->pageLoader = $pageLoader;
        $this->sitemapLoader = $sitemapLoader;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->searchSubManager = $searchSubManager;
        $this->pageRoute = $pageRoute;
    }

    public function create(StructureItem $structureItem, AdminItem $parent = null): AdminItem
    {
        return new AdminItem(
            $structureItem,
            $this,
            $this->pageLoader,
            $this->sitemapLoader,
            $this->pageTypeSubManager,
            $this->searchSubManager,
            $this->pageRoute,
            $parent
        );
    }

    public function pageTypeSubManager(): PageTypeSubManager
    {
        return $this->pageTypeSubManager;
    }
}
