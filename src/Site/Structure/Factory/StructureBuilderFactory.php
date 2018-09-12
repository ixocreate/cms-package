<?php
namespace KiwiSuite\Cms\Site\Structure\Factory;

use KiwiSuite\Cms\Site\Structure\StructureBuilder;
use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Database\EntityManager\Factory\EntityManagerSubManager;

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
        return new StructureBuilder(
            $container->get(EntityManagerSubManager::class)->get('master')
        );
    }
}