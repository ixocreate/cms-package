<?php
namespace KiwiSuite\Cms\Loader\Factory;

use KiwiSuite\Cms\Loader\DatabaseSitemapLoader;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Database\Repository\Factory\RepositorySubManager;

final class DatabaseSitemapLoaderFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        return new DatabaseSitemapLoader(
            $container->get(RepositorySubManager::class)->get(SitemapRepository::class)
        );
    }
}