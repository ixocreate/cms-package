<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\BootstrapItem;

use Ixocreate\Package\Cms\Config\Configurator;
use Ixocreate\Application\BootstrapItemInterface;
use Ixocreate\Application\ConfiguratorInterface;

final class CmsBootstrapItem implements BootstrapItemInterface
{
    /**
     * @return ConfiguratorInterface
     */
    public function getConfigurator(): ConfiguratorInterface
    {
        return new Configurator();
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return 'cms';
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return 'cms.php';
    }
}
