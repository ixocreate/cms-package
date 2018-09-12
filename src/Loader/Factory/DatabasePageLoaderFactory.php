<?php
namespace KiwiSuite\Cms\Loader\Factory;

use KiwiSuite\Cms\Loader\DatabasePageLoader;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Database\Repository\Factory\RepositorySubManager;

final class DatabasePageLoaderFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        return new DatabasePageLoader(
            $container->get(RepositorySubManager::class)->get(PageRepository::class)
        );
    }
}