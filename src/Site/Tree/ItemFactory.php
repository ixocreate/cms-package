<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Site\Tree;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\PageContentCacheable;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\PageType\PageTypeSubManager;
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
     * ItemFactory constructor.
     * @param CacheableInterface $pageCacheable
     * @param CacheableInterface $sitemapCacheable
     * @param CacheableInterface $pageVersionCacheable
     * @param CacheManager $cacheManager
     * @param SubManagerInterface $pageTypeSubManager
     */
    public function __construct(
        CacheableInterface $pageCacheable,
        CacheableInterface $sitemapCacheable,
        CacheableInterface $pageVersionCacheable,
        CacheManager $cacheManager,
        SubManagerInterface $pageTypeSubManager
    ) {

        $this->pageCacheable = $pageCacheable;
        $this->sitemapCacheable = $sitemapCacheable;
        $this->cacheManager = $cacheManager;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->pageVersionCacheable = $pageVersionCacheable;
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
            $this->pageTypeSubManager
        );
    }
}
