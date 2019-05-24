<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Loader\Factory;

use Ixocreate\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Database\Repository\Factory\RepositorySubManager;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

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
