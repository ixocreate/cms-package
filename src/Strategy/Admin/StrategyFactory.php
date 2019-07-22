<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Admin;

use Ixocreate\Cache\CacheSubManager;
use Ixocreate\Cms\Repository\NavigationRepository;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Database\EntityManager\Factory\EntityManagerSubManager;
use Ixocreate\Database\Repository\Factory\RepositorySubManager;
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
        $loader = new Loader(
            $container->get(RepositorySubManager::class)->get(SitemapRepository::class),
            $container->get(RepositorySubManager::class)->get(PageRepository::class),
            $container->get(RepositorySubManager::class)->get(NavigationRepository::class)
        );

        return new Strategy(
            $loader
        );
    }
}
