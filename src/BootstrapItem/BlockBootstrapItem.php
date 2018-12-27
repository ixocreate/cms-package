<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\BootstrapItem;

use Ixocreate\Cms\Block\BlockConfigurator;
use Ixocreate\Contract\Application\BootstrapItemInterface;
use Ixocreate\Contract\Application\ConfiguratorInterface;

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
