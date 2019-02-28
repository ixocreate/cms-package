<?php
declare(strict_types=1);
namespace Ixocreate\Cms;

use Ixocreate\Cms\Block\BlockSubManager;
use Ixocreate\Cms\Loader\DatabasePageLoader;
use Ixocreate\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\Loader\Factory\DatabasePageLoaderFactory;
use Ixocreate\Cms\Loader\Factory\DatabaseSitemapLoaderFactory;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Cms\Router\Factory\CmsRouterFactory;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Cms\Seo\Sitemap\PageProvider;
use Ixocreate\Cms\Seo\Sitemap\XmlSitemapProviderSubManager;
use Ixocreate\Cms\Site\Admin\Builder;
use Ixocreate\Cms\Site\Structure\Factory\StructureBuilderFactory;
use Ixocreate\Cms\Site\Structure\StructureBuilder;
use Ixocreate\ServiceManager\ServiceManagerConfigurator;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addSubManager(PageTypeSubManager::class);
$serviceManager->addSubManager(BlockSubManager::class);
$serviceManager->addSubManager(XmlSitemapProviderSubManager::class);

$serviceManager->addFactory(CmsRouter::class, CmsRouterFactory::class);
$serviceManager->addFactory(PageRoute::class);

$serviceManager->addService(DatabasePageLoader::class, DatabasePageLoaderFactory::class);
$serviceManager->addService(DatabaseSitemapLoader::class, DatabaseSitemapLoaderFactory::class);
$serviceManager->addService(StructureBuilder::class, StructureBuilderFactory::class);
$serviceManager->addService(Builder::class);
