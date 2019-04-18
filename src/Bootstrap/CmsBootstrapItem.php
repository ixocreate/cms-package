<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Bootstrap;

use Ixocreate\Cms\Package\Config\Configurator;
use Ixocreate\Application\Service\Bootstrap\BootstrapItemInterface;
use Ixocreate\Application\Service\Configurator\ConfiguratorInterface;

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
