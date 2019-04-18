<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms;

use Ixocreate\Package\Cms\BootstrapItem\BlockBootstrapItem;
use Ixocreate\Package\Cms\BootstrapItem\CmsBootstrapItem;
use Ixocreate\Package\Cms\BootstrapItem\PageTypeBootstrapItem;
use Ixocreate\Application\ConfiguratorRegistryInterface;
use Ixocreate\Application\PackageInterface;
use Ixocreate\Application\ServiceRegistryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

final class Package implements PackageInterface
{
    /**
     * @param ConfiguratorRegistryInterface $configuratorRegistry
     */
    public function configure(ConfiguratorRegistryInterface $configuratorRegistry): void
    {
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     */
    public function addServices(ServiceRegistryInterface $serviceRegistry): void
    {
    }

    /**
     * @return array|null
     */
    public function getBootstrapItems(): ?array
    {
        return [
            PageTypeBootstrapItem::class,
            BlockBootstrapItem::class,
            CmsBootstrapItem::class,
        ];
    }

    /**
     * @return array|null
     */
    public function getConfigProvider(): ?array
    {
        return null;
    }

    /**
     * @param ServiceManagerInterface $serviceManager
     */
    public function boot(ServiceManagerInterface $serviceManager): void
    {
        // TODO: Implement boot() method.
    }

    /**
     * @return null|string
     */
    public function getBootstrapDirectory(): ?string
    {
        return  __DIR__ . '/../bootstrap';
    }

    /**
     * @return null|string
     */
    public function getConfigDirectory(): ?string
    {
        return  __DIR__ . '/../config';
    }

    /**
     * @return array|null
     */
    public function getDependencies(): ?array
    {
        return null;
    }
}
