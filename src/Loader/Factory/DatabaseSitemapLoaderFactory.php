<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Loader\Factory;

use Ixocreate\Package\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Package\Cms\Repository\SitemapRepository;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\Package\Database\Repository\Factory\RepositorySubManager;

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
