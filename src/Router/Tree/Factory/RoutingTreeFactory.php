<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Router\Tree\Factory;

use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Strategy\Strategy;
use Ixocreate\Cms\Tree\Mutatable\MutatableSubManager;
use Ixocreate\Cms\Tree\Searchable\SearchableSubManager;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

final class RoutingTreeFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        return new \Ixocreate\Cms\Router\Tree\RoutingTreeFactory(
            $container->get(Strategy::class),
            $container->get(ReplacementManager::class),
            $container->get(PageTypeSubManager::class),
            $container->get(MutatableSubManager::class),
            $container->get(SearchableSubManager::class)
        );
    }
}
