<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Essential;

use Ixocreate\Cache\CacheSubManager;
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
        $cache = $container->get(CacheSubManager::class)->get('cms_store');

        $loader = new Loader($cache);
        $persister = new Persister(
            $container->get(EntityManagerSubManager::class)->get('master'),
            $cache
        );

        return new Strategy(
            $loader,
            $persister
        );
    }
}
