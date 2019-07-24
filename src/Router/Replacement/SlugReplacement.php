<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router\Replacement;

use Ixocreate\Cms\Router\RouteSpecification;
use Ixocreate\Cms\Router\Tree\RoutingItem;

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
     * @throws \Exception
     * @throws \Psr\Cache\InvalidArgumentException
     * @return RouteSpecification
     */
    public function replace(
        RouteSpecification $routeSpecification,
        string $locale,
        RoutingItem $item
    ): void {
        $slug = $item->page($locale)->slug();
        if (!empty($slug)) {
            foreach ($routeSpecification->uris() as $name => $uri) {
                $routeSpecification->addUri(\str_replace('${SLUG}', $slug, $uri), $name);
            }
        }
    }
}
