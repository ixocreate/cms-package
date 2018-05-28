<?php
declare(strict_types=1);
namespace KiwiSuite\Cms;

use KiwiSuite\Cms\Block\BlockSubManager;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addSubManager(PageTypeSubManager::class);
$serviceManager->addSubManager(BlockSubManager::class);
