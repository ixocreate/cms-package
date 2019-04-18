<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Site\Structure\Factory;

use Ixocreate\Package\Cms\Site\Structure\StructureBuilder;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\Package\Database\EntityManager\Factory\EntityManagerSubManager;

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
