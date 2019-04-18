<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Bootstrap;

use Ixocreate\Cms\Package\Block\BlockConfigurator;
use Ixocreate\Application\Service\Bootstrap\BootstrapItemInterface;
use Ixocreate\Application\Service\Configurator\ConfiguratorInterface;

final class BlockBootstrapItem implements BootstrapItemInterface
{
    /**
     * @return ConfiguratorInterface
     */
    public function getConfigurator(): ConfiguratorInterface
    {
        return new BlockConfigurator();
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return 'block';
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return 'block.php';
    }
}
