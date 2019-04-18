<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Bootstrap;

use Ixocreate\Cms\Package\PageType\PageTypeConfigurator;
use Ixocreate\Application\Service\Bootstrap\BootstrapItemInterface;
use Ixocreate\Application\Service\Configurator\ConfiguratorInterface;

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
