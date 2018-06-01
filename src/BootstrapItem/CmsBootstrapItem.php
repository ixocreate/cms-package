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

namespace KiwiSuite\Cms\BootstrapItem;

use KiwiSuite\Cms\Block\BlockConfigurator;
use KiwiSuite\Cms\Config\Configurator;
use KiwiSuite\Contract\Application\BootstrapItemInterface;
use KiwiSuite\Contract\Application\ConfiguratorInterface;

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
