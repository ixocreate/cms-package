<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Loader\Factory;

use Ixocreate\Cms\Package\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\Package\Repository\SitemapRepository;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\Database\Package\Repository\Factory\RepositorySubManager;

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
