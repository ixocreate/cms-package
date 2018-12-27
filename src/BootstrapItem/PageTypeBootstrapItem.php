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
