<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\PageType;

use Ixocreate\Application\Bootstrap\BootstrapItemInterface;
use Ixocreate\Application\Configurator\ConfiguratorInterface;

final class PageTypeBootstrapItem implements BootstrapItemInterface
{
    /**
     * @return ConfiguratorInterface
     */
    public function getConfigurator(): ConfiguratorInterface
    {
        return new PageTypeConfigurator();
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return 'pageType';
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return 'page-type.php';
    }
}
