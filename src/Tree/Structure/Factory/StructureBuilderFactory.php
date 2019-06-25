<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree\Structure\Factory;

use Ixocreate\Cms\Tree\Structure\StructureBuilder;
use Ixocreate\Database\EntityManager\Factory\EntityManagerSubManager;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

final class StructureBuilderFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        return new StructureBuilder($container->get(EntityManagerSubManager::class)->get('master'));
    }
}
