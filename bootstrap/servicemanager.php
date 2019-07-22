<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Application\Service\ServiceManagerConfigurator;
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
use Ixocreate\Cms\Seo\Sitemap\XmlSitemapProviderSubManager;
use Ixocreate\Cms\Site\Structure\Factory\StructureBuilderFactory;
use Ixocreate\Cms\Site\Structure\StructureBuilder;
use Ixocreate\Cms\Site\Tree\Container;
use Ixocreate\Cms\Site\Tree\Factory\ContainerFactory;
use Ixocreate\Cms\Site\Tree\SearchSubManager;
use Ixocreate\Cms\Strategy\Strategy;
use Ixocreate\Cms\Strategy\StrategyFactory;
use Ixocreate\Cms\Tree\AdminTreeFactory;
use Ixocreate\Cms\Tree\Mutatable\MutatableSubManager;
use Ixocreate\Cms\Tree\Searchable\SearchableSubManager;

/** @var ServiceManagerConfigurator $serviceManager */
$serviceManager->addSubManager(PageTypeSubManager::class);
$serviceManager->addSubManager(BlockSubManager::class);
$serviceManager->addSubManager(XmlSitemapProviderSubManager::class);
$serviceManager->addSubManager(SearchSubManager::class);
$serviceManager->addSubManager(ReplacementManager::class);
$serviceManager->addSubManager(MutatableSubManager::class);
$serviceManager->addSubManager(SearchableSubManager::class);

$serviceManager->addFactory(CmsRouter::class, CmsRouterFactory::class);
$serviceManager->addFactory(PageRoute::class);
$serviceManager->addFactory(Container::class, ContainerFactory::class);

$serviceManager->addService(DatabasePageLoader::class, DatabasePageLoaderFactory::class);
$serviceManager->addService(DatabaseSitemapLoader::class, DatabaseSitemapLoaderFactory::class);
$serviceManager->addService(StructureBuilder::class, StructureBuilderFactory::class);

$serviceManager->addFactory(Strategy::class, StrategyFactory::class);
$serviceManager->addFactory(AdminTreeFactory::class, \Ixocreate\Cms\Tree\Factory\AdminTreeFactory::class);
$serviceManager->addFactory(\Ixocreate\Cms\Strategy\Full\Strategy::class, \Ixocreate\Cms\Strategy\Full\StrategyFactory::class);
$serviceManager->addFactory(\Ixocreate\Cms\Strategy\Essential\Strategy::class, \Ixocreate\Cms\Strategy\Essential\StrategyFactory::class);
$serviceManager->addFactory(\Ixocreate\Cms\Strategy\Admin\Strategy::class, \Ixocreate\Cms\Strategy\Admin\StrategyFactory::class);
