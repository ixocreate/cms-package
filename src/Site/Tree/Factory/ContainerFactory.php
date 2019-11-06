<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Tree\Factory;

use Ixocreate\Cache\CacheableSubManager;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\StructureCacheable;
use Ixocreate\Cms\Site\Tree\Container;
use Ixocreate\Cms\Site\Tree\ItemFactory;
use Ixocreate\Cms\Site\Tree\SearchSubManager;
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
        $searchSubManager = $container->get(SearchSubManager::class);

        $itemFactory = $container->get(ItemFactory::class);

        return new Container(
            $cachemanager->fetch($structureCacheable),
            $searchSubManager,
            $itemFactory
        );
    }
}
