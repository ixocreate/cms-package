<?php
declare(strict_types=1);
namespace KiwiSuite\Cms;

use KiwiSuite\Cms\Block\BlockSubManager;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Router\CmsRouter;
use KiwiSuite\Cms\Router\Factory\CmsRouterFactory;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addSubManager(PageTypeSubManager::class);
$serviceManager->addSubManager(BlockSubManager::class);

$serviceManager->addFactory(CmsRouter::class, CmsRouterFactory::class);
