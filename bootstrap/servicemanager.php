<?php
declare(strict_types=1);
namespace Ixocreate\Package\Cms;

use Ixocreate\Package\Cms\Block\BlockSubManager;
use Ixocreate\Package\Cms\Loader\DatabasePageLoader;
use Ixocreate\Package\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Package\Cms\Loader\Factory\DatabasePageLoaderFactory;
use Ixocreate\Package\Cms\Loader\Factory\DatabaseSitemapLoaderFactory;
use Ixocreate\Package\Cms\PageType\PageTypeSubManager;
use Ixocreate\Package\Cms\Router\CmsRouter;
use Ixocreate\Package\Cms\Router\Factory\CmsRouterFactory;
use Ixocreate\Package\Cms\Router\PageRoute;
use Ixocreate\Package\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Package\Cms\Seo\Sitemap\XmlSitemapProviderSubManager;
use Ixocreate\Package\Cms\Site\Admin\Builder;
use Ixocreate\Package\Cms\Site\Structure\Factory\StructureBuilderFactory;
use Ixocreate\Package\Cms\Site\Structure\StructureBuilder;
use Ixocreate\Package\Cms\Site\Tree\Container;
use Ixocreate\Package\Cms\Site\Tree\Factory\ContainerFactory;
use Ixocreate\Package\Cms\Site\Tree\SearchSubManager;
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
