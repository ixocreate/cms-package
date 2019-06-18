<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Test\Cms;

use Ixocreate\Application\Service\ServiceManagerConfig;
use Ixocreate\Application\Service\ServiceManagerConfigurator;
use Ixocreate\Application\Service\ServiceRegistry;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Tree\Factory;
use Ixocreate\Cms\Tree\ItemInterface;
use Ixocreate\Cms\Tree\Structure\StructureStore;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\Type;
use Ixocreate\Schema\Type\TypeConfigurator;
use Ixocreate\Schema\Type\TypeInterface;
use Ixocreate\Schema\Type\TypeSubManager;
use Ixocreate\Schema\Type\UuidType;
use Ixocreate\ServiceManager\ServiceManager;
use Ixocreate\ServiceManager\ServiceManagerSetup;

\chdir(\dirname(__DIR__) . '/../');
include 'vendor/autoload.php';

$serviceManager = new ServiceManager(
    new ServiceManagerConfig(new ServiceManagerConfigurator()),
    new ServiceManagerSetup(),
    []
);

$pageTypeSubManager = new PageTypeSubManager(
    $serviceManager,
    new ServiceManagerConfig(new ServiceManagerConfigurator()),
    PageTypeInterface::class
);

$typeConfigurator = new TypeConfigurator();
$typeConfigurator->addType(UuidType::class);
$typeConfigurator->addType(DateTimeType::class);
$serviceRegistry = new ServiceRegistry();
$typeConfigurator->registerService($serviceRegistry);

$typeSubManager = new TypeSubManager(
    $serviceManager,
    $serviceRegistry->get(TypeSubManager::class . '::Config'),
    TypeInterface::class
);
Type::initialize($typeSubManager);

$structure = (new StructureStore(include 'tree.php'))->structure();
$factory = new Factory($pageTypeSubManager);
$container = $factory->createContainer($structure);

$container = $container->where(function (ItemInterface $item) {
    if (!$item->hasPage('de_AT')) {
        return false;
    }
    return $item->page('de_AT')->status() === 'offline';
});
\var_dump($container->count());
