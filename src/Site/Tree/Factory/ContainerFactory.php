<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Site\Tree\Factory;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Package\Cacheable\PageCacheable;
use Ixocreate\Cms\Package\Cacheable\PageVersionCacheable;
use Ixocreate\Cms\Package\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Package\Cacheable\StructureCacheable;
use Ixocreate\Cms\Package\PageType\PageTypeSubManager;
use Ixocreate\Cms\Package\Router\PageRoute;
use Ixocreate\Cms\Package\Site\Tree\Container;
use Ixocreate\Cms\Package\Site\Tree\ItemFactory;
use Ixocreate\Cms\Package\Site\Tree\SearchSubManager;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\Cache\Package\CacheableSubManager;

final class ContainerFactory implements FactoryInterface
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
        $structureCacheable = $container->get(CacheableSubManager::class)->get(StructureCacheable::class);
        $pageTypeSubManager = $container->get(PageTypeSubManager::class);
        $searchSubManager = $container->get(SearchSubManager::class);
        $pageRoute = $container->get(PageRoute::class);

        $itemFactory = new ItemFactory(
            $pageCacheable,
            $sitemapCacheable,
            $pageVersionCacheable,
            $cachemanager,
            $pageTypeSubManager,
            $searchSubManager,
            $pageRoute
        );

        return new Container(
            $cachemanager->fetch($structureCacheable),
            $searchSubManager,
            $itemFactory
        );
    }
}
