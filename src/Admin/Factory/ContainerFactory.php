<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Admin\Factory;

use Ixocreate\Cms\Admin\Factory;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Cms\Tree\FilterManager;
use Ixocreate\Cms\Tree\Structure\StructureBuilder;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

final class ContainerFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $factory = new Factory(
            $container->get(PageTypeSubManager::class),
            $container->get(FilterManager::class),
            $container->get(LocaleManager::class),
            $container->get(CmsRouter::class)
        );

        /** @var StructureBuilder $structureBuilder */
        $structureBuilder = $container->get(StructureBuilder::class);

        return $factory->createContainer($structureBuilder->build());
    }
}
