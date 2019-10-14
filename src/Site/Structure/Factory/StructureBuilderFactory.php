<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Structure\Factory;

use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Site\Structure\StructureBuilder;
use Ixocreate\Cms\Site\Structure\StructureLoader;
use Ixocreate\Database\EntityManager\Factory\EntityManagerSubManager;
use Ixocreate\Database\Repository\Factory\RepositorySubManager;
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
        return new StructureBuilder(
            $container->get(EntityManagerSubManager::class)->get('master'),
            $container->get(PageTypeSubManager::class),
            $container->get(RepositorySubManager::class)->get(SitemapRepository::class),
            $container->get(StructureLoader::class)
        );
    }
}
