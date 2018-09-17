<?php
declare(strict_types=1);
namespace KiwiSuite\Cms;

use KiwiSuite\Cms\Block\BlockSubManager;
use KiwiSuite\Cms\Loader\DatabasePageLoader;
use KiwiSuite\Cms\Loader\DatabaseSitemapLoader;
use KiwiSuite\Cms\Loader\Factory\DatabasePageLoaderFactory;
use KiwiSuite\Cms\Loader\Factory\DatabaseSitemapLoaderFactory;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Router\CmsRouter;
use KiwiSuite\Cms\Router\Factory\CmsRouterFactory;
use KiwiSuite\Cms\Router\PageRoute;
use KiwiSuite\Cms\Site\Admin\Builder;
use KiwiSuite\Cms\Site\Structure\Factory\StructureBuilderFactory;
use KiwiSuite\Cms\Site\Structure\StructureBuilder;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addSubManager(PageTypeSubManager::class);
$serviceManager->addSubManager(BlockSubManager::class);

$serviceManager->addFactory(CmsRouter::class, CmsRouterFactory::class);
$serviceManager->addFactory(PageRoute::class);

$serviceManager->addService(DatabasePageLoader::class, DatabasePageLoaderFactory::class);
$serviceManager->addService(DatabaseSitemapLoader::class, DatabaseSitemapLoaderFactory::class);
$serviceManager->addService(StructureBuilder::class, StructureBuilderFactory::class);
$serviceManager->addService(Builder::class);
