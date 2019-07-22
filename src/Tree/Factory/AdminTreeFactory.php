<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Tree\Factory;

use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Strategy\Admin\Strategy;
use Ixocreate\Cms\Tree\Mutatable\MutatableSubManager;
use Ixocreate\Cms\Tree\Searchable\SearchableSubManager;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

final class AdminTreeFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        return new \Ixocreate\Cms\Tree\AdminTreeFactory(
            $container->get(Strategy::class),
            $container->get(LocaleManager::class),
            $container->get(PageTypeSubManager::class),
            $container->get(MutatableSubManager::class),
            $container->get(SearchableSubManager::class)
        );
    }
}
