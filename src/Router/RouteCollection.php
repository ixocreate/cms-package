<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Router;

use Ixocreate\Cms\Router\Tree\RoutingContainer;
use Ixocreate\Cms\Router\Tree\RoutingItem;
use Ixocreate\Intl\LocaleManager;
use RecursiveIteratorIterator;

final class RouteCollection
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    public function build(RoutingContainer $routingContainer): \Symfony\Component\Routing\RouteCollection
    {
        $routeCollection = new \Symfony\Component\Routing\RouteCollection();

        $iterator = new \RecursiveIteratorIterator($routingContainer, RecursiveIteratorIterator::SELF_FIRST);
        /** @var RoutingItem $routingItem */
        foreach ($iterator as $routingItem) {
            foreach ($this->localeManager->allActive() as $locale) {
                $routingItem->pageRoute($locale['locale']);
            }
        }
        $iterator->rewind();
        /** @var RoutingItem $routingItem */
        foreach ($iterator as $routingItem) {
            foreach ($this->localeManager->allActive() as $locale) {
                $locale = $locale['locale'];

                $routeSpecification = $routingItem->pageRoute($locale);
                if (empty($routeSpecification)) {
                    continue;
                }
                if (!$routeSpecification->isRoot()) {
                    continue;
                }

                $routeSpecification->addToRouteCollection($routeCollection, $locale);
            }
        }

        return $routeCollection;
    }
}
