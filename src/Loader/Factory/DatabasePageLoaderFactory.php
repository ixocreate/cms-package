<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Loader\Factory;

use Ixocreate\Package\Cms\Loader\DatabasePageLoader;
use Ixocreate\Package\Cms\Repository\PageRepository;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\Package\Database\Repository\Factory\RepositorySubManager;

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
