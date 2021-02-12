<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Application\ServiceManager\ServiceManagerConfigurator;
use Ixocreate\Cms\Block\BlockSubManager;
use Ixocreate\Cms\Loader\DatabasePageLoader;
use Ixocreate\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\Loader\Factory\DatabasePageLoaderFactory;
use Ixocreate\Cms\Loader\Factory\DatabaseSitemapLoaderFactory;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Cms\Router\Factory\CmsRouterFactory;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Router\RouteCollection;
use Ixocreate\Cms\Seo\Sitemap\XmlSitemapProviderSubManager;
use Ixocreate\Cms\Site\Structure\Factory\StructureBuilderFactory;
use Ixocreate\Cms\Site\Structure\StructureBuilder;
use Ixocreate\Cms\Site\Structure\StructureLoader;
use Ixocreate\Cms\Site\Tree\Container;
use Ixocreate\Cms\Site\Tree\Factory\ContainerFactory;
use Ixocreate\Cms\Site\Tree\Factory\ItemFactoryFactory;
use Ixocreate\Cms\Site\Tree\ItemFactory;
use Ixocreate\Cms\Site\Tree\SearchSubManager;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addSubManager(PageTypeSubManager::class);
$serviceManager->addSubManager(BlockSubManager::class);
$serviceManager->addSubManager(XmlSitemapProviderSubManager::class);
$serviceManager->addSubManager(SearchSubManager::class);
$serviceManager->addSubManager(ReplacementManager::class);

$serviceManager->addFactory(CmsRouter::class, CmsRouterFactory::class);
$serviceManager->addFactory(PageRoute::class);
$serviceManager->addFactory(RouteCollection::class);
$serviceManager->addFactory(Container::class, ContainerFactory::class);
$serviceManager->addFactory(ItemFactory::class, ItemFactoryFactory::class);

$serviceManager->addService(DatabasePageLoader::class, DatabasePageLoaderFactory::class);
$serviceManager->addService(DatabaseSitemapLoader::class, DatabaseSitemapLoaderFactory::class);
$serviceManager->addService(StructureBuilder::class, StructureBuilderFactory::class);
$serviceManager->addService(StructureLoader::class);
$serviceManager->addService(Site\Admin\StructureLoader::class);
