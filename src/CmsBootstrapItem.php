<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Application\Bootstrap\BootstrapItemInterface;
use Ixocreate\Application\Configurator\ConfiguratorInterface;

final class CmsBootstrapItem implements BootstrapItemInterface
{
    /**
     * @return ConfiguratorInterface
     */
    public function getConfigurator(): ConfiguratorInterface
    {
        return new CmsConfigurator();
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
