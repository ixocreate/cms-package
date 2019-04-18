<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Package;

use Ixocreate\Cms\Package\Block\BlockSubManager;
use Ixocreate\Cms\Package\Loader\DatabasePageLoader;
use Ixocreate\Cms\Package\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\Package\Loader\Factory\DatabasePageLoaderFactory;
use Ixocreate\Cms\Package\Loader\Factory\DatabaseSitemapLoaderFactory;
use Ixocreate\Cms\Package\PageType\PageTypeSubManager;
use Ixocreate\Cms\Package\Router\CmsRouter;
use Ixocreate\Cms\Package\Router\Factory\CmsRouterFactory;
use Ixocreate\Cms\Package\Router\PageRoute;
use Ixocreate\Cms\Package\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Package\Seo\Sitemap\XmlSitemapProviderSubManager;
use Ixocreate\Cms\Package\Site\Admin\Builder;
use Ixocreate\Cms\Package\Site\Structure\Factory\StructureBuilderFactory;
use Ixocreate\Cms\Package\Site\Structure\StructureBuilder;
use Ixocreate\Cms\Package\Site\Tree\Container;
use Ixocreate\Cms\Package\Site\Tree\Factory\ContainerFactory;
use Ixocreate\Cms\Package\Site\Tree\SearchSubManager;
use Ixocreate\ServiceManager\ServiceManagerConfigurator;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addSubManager(PageTypeSubManager::class);
$serviceManager->addSubManager(BlockSubManager::class);
$serviceManager->addSubManager(XmlSitemapProviderSubManager::class);
$serviceManager->addSubManager(SearchSubManager::class);
$serviceManager->addSubManager(ReplacementManager::class);

$serviceManager->addFactory(CmsRouter::class, CmsRouterFactory::class);
$serviceManager->addFactory(PageRoute::class);
$serviceManager->addFactory(Container::class, ContainerFactory::class);

$serviceManager->addService(DatabasePageLoader::class, DatabasePageLoaderFactory::class);
$serviceManager->addService(DatabaseSitemapLoader::class, DatabaseSitemapLoaderFactory::class);
$serviceManager->addService(StructureBuilder::class, StructureBuilderFactory::class);
$serviceManager->addService(Builder::class);
