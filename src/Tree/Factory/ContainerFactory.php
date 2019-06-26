<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree\Factory;

use Ixocreate\Cache\CacheableSubManager;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\StructureCacheable;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Tree\Factory;
use Ixocreate\Cms\Tree\FilterManager;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

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
        $structureCacheable = $container->get(CacheableSubManager::class)->get(StructureCacheable::class);

        $factory = new Factory(
            $container->get(PageTypeSubManager::class),
            $container->get(FilterManager::class)
        );

        return $factory->createContainer($cachemanager->fetch($structureCacheable));
    }
}
