<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Package\Router\Replacement;

use Ixocreate\Cms\Package\Router\RouteSpecification;
use Ixocreate\Cms\Package\Router\RoutingItem;

final class SlugReplacement implements ReplacementInterface
{
    /**
     * @return int
     */
    public function priority(): int
    {
        return 1;
    }

    /**
     * @param RouteSpecification $routeSpecification
     * @param string $locale
     * @param RoutingItem $item
     * @return RouteSpecification
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function replace(
        RouteSpecification $routeSpecification,
        string $locale,
        RoutingItem $item
    ): RouteSpecification {

        $page = $item->page($locale);
        if (!empty($page->slug())) {
            foreach ($routeSpecification->uris() as $name => $uri) {
                $routeSpecification = $routeSpecification->withUri(\str_replace('${SLUG}', $page->slug(), $uri), $name);
            }
        }

        return $routeSpecification;
    }
}
