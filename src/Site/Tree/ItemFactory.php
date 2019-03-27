<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Tree;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Cms\Site\Structure\StructureItem;
use Ixocreate\Contract\Cache\CacheableInterface;
use Ixocreate\Contract\ServiceManager\SubManager\SubManagerInterface;

final class ItemFactory
{
    /**
     * @var PageCacheable
     */
    private $pageCacheable;

    /**
     * @var SitemapCacheable
     */
    private $sitemapCacheable;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var CacheableInterface
     */
    private $pageVersionCacheable;

    /**
     * @var SubManagerInterface
     */
    private $searchSubManager;

    /**
     * @var PageRoute
     */
    private $pageRoute;

    /**
     * ItemFactory constructor.
     * @param CacheableInterface $pageCacheable
     * @param CacheableInterface $sitemapCacheable
     * @param CacheableInterface $pageVersionCacheable
     * @param CacheManager $cacheManager
     * @param SubManagerInterface $pageTypeSubManager
     * @param SubManagerInterface $searchSubManager
     * @param PageRoute $pageRoute
     */
    public function __construct(
        CacheableInterface $pageCacheable,
        CacheableInterface $sitemapCacheable,
        CacheableInterface $pageVersionCacheable,
        CacheManager $cacheManager,
        SubManagerInterface $pageTypeSubManager,
        SubManagerInterface $searchSubManager,
        PageRoute $pageRoute
    ) {
        $this->pageCacheable = $pageCacheable;
        $this->sitemapCacheable = $sitemapCacheable;
        $this->cacheManager = $cacheManager;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->pageVersionCacheable = $pageVersionCacheable;
        $this->searchSubManager = $searchSubManager;
        $this->pageRoute = $pageRoute;
    }

    public function create(StructureItem $structureItem): Item
    {
        return new Item(
            $structureItem,
            $this,
            $this->pageCacheable,
            $this->sitemapCacheable,
            $this->pageVersionCacheable,
            $this->cacheManager,
            $this->pageTypeSubManager,
            $this->searchSubManager,
            $this->pageRoute
        );
    }
}
