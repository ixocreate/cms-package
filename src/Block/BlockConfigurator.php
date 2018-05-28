<?php
/**
 * kiwi-suite/cms (https://github.com/kiwi-suite/cms)
 *
 * @package kiwi-suite/cms
 * @see https://github.com/kiwi-suite/cms
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */
declare(strict_types=1);

namespace KiwiSuite\Cms\Block;

use KiwiSuite\Contract\Application\ConfiguratorInterface;
use KiwiSuite\Contract\Application\ServiceRegistryInterface;
use KiwiSuite\ServiceManager\Factory\AutowireFactory;
use KiwiSuite\ServiceManager\SubManager\SubManagerConfigurator;

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
     * @return BlockMapping
     */
    public function getBlockMapping()
    {
        $config = $this->subManagerConfigurator;

        $factories = $config->getServiceManagerConfig()->getFactories();

        $mapping = [];
        foreach ($factories as $id => $factory) {
            if (!\is_subclass_of($id, BlockInterface::class, true)) {
                throw new \InvalidArgumentException(\sprintf("'%s' doesn't implement '%s'", $id, BlockInterface::class));
            }
            $name = \forward_static_call([$id, 'name']);
            $mapping[$name] = $id;
        }

        return new BlockMapping($mapping);
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     */
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $serviceRegistry->add(BlockMapping::class, $this->getBlockMapping());
        $this->subManagerConfigurator->registerService($serviceRegistry);
    }
}
