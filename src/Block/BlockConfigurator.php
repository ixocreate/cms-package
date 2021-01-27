<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Block;

use Ixocreate\Application\Configurator\ConfiguratorInterface;
use Ixocreate\Application\Service\ServiceRegistryInterface;
use Ixocreate\Application\ServiceManager\SubManagerConfigurator;
use Ixocreate\ServiceManager\Factory\AutowireFactory;

final class BlockConfigurator implements ConfiguratorInterface
{
    /**
     * @var SubManagerConfigurator
     */
    private $subManagerConfigurator;

    /**
     * BlockConfigurator constructor.
     */
    public function __construct()
    {
        $this->subManagerConfigurator = new SubManagerConfigurator(BlockSubManager::class, BlockInterface::class);
    }

    /**
     * @return SubManagerConfigurator
     */
    public function getManagerConfigurator()
    {
        return $this->subManagerConfigurator;
    }

    /**
     * @param string $directory
     * @param bool $recursive
     */
    public function addDirectory(string $directory, bool $recursive)
    {
        $this->subManagerConfigurator->addDirectory($directory, $recursive);
    }

    /**
     * @param string $block
     * @param string $factory
     */
    public function addBlock(string $block, string $factory = AutowireFactory::class)
    {
        $this->subManagerConfigurator->addFactory($block, $factory);
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     */
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $this->subManagerConfigurator->registerService($serviceRegistry);
    }
}
