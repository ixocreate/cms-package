<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Site\Tree\Factory;

use Ixocreate\Cache\CacheableSubManager;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\PageVersionCacheable;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Cacheable\StructureCacheable;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Cms\Site\Tree\ItemFactory;
use Ixocreate\Cms\Site\Tree\SearchSubManager;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

final class ItemFactoryFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $cachemanager = $container->get(CacheManager::class);
        $pageCacheable = $container->get(CacheableSubManager::class)->get(PageCacheable::class);
        $sitemapCacheable = $container->get(CacheableSubManager::class)->get(SitemapCacheable::class);
        $pageVersionCacheable = $container->get(CacheableSubManager::class)->get(PageVersionCacheable::class);
        $pageTypeSubManager = $container->get(PageTypeSubManager::class);
        $searchSubManager = $container->get(SearchSubManager::class);
        $pageRoute = $container->get(PageRoute::class);

        return new ItemFactory(
            $pageCacheable,
            $sitemapCacheable,
            $pageVersionCacheable,
            $cachemanager,
            $pageTypeSubManager,
            $searchSubManager,
            $pageRoute
        );
    }
}
