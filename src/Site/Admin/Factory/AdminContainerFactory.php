<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Admin\Factory;

use Ixocreate\Cms\Loader\DatabasePageLoader;
use Ixocreate\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Cms\Site\Admin\AdminContainer;
use Ixocreate\Cms\Site\Admin\AdminItemFactory;
use Ixocreate\Cms\Site\Admin\AdminSearchSubManager;
use Ixocreate\Cms\Site\Structure\StructureBuilder;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

final class AdminContainerFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $pageLoader = $container->get(DatabasePageLoader::class);
        $sitemapLoader = $container->get(DatabaseSitemapLoader::class);
        $structureBuilder = $container->get(StructureBuilder::class);
        $pageTypeSubManager = $container->get(PageTypeSubManager::class);
        $searchSubManager = $container->get(AdminSearchSubManager::class);
        $pageRoute = $container->get(PageRoute::class);

        $itemFactory = new AdminItemFactory(
            $pageLoader,
            $sitemapLoader,
            $pageTypeSubManager,
            $searchSubManager,
            $pageRoute
        );

        return new AdminContainer(
            $structureBuilder->build()->structure(),
            $searchSubManager,
            $itemFactory
        );
    }
}
