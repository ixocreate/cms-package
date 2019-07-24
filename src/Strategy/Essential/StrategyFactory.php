<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Essential;

use Ixocreate\Cache\CacheableSubManager;
use Ixocreate\Cache\CacheInterface;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cache\CacheSubManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Database\EntityManager\Factory\EntityManagerSubManager;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

final class StrategyFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        /** @var CacheInterface $cache */
        $cache = $container->get(CacheSubManager::class)->get('cms_store');

        $loader = new Loader(
            $cache,
            $container->get(CacheableSubManager::class)->get(PageCacheable::class),
            $container->get(CacheManager::class)
        );
        $persister = new Persister(
            $container->get(EntityManagerSubManager::class)->get('master'),
            $cache,
            $container->get(CacheManager::class),
            $container->get(CacheableSubManager::class)->get(PageCacheable::class)
        );

        if (!$cache->has(Strategy::CACHE_KEY)) {
            $persister->persistSitemap();
        }

        return new Strategy(
            $loader,
            $persister
        );
    }
}
