<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\BootstrapItem;

use Ixocreate\Cms\PageType\PageTypeConfigurator;
use Ixocreate\Contract\Application\BootstrapItemInterface;
use Ixocreate\Contract\Application\ConfiguratorInterface;

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
