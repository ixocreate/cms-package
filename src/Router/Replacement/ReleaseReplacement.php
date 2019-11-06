<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router\Replacement;

use Ixocreate\Cms\Router\RouteSpecification;
use Ixocreate\Cms\Router\RoutingItem;

final class ReleaseReplacement implements ReplacementInterface
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
     */
    public function replace(
        RouteSpecification $routeSpecification,
        string $locale,
        RoutingItem $item
    ): RouteSpecification {
        foreach ($routeSpecification->uris() as $name => $uri) {
            if (!empty(\preg_match('/\${RELEASE:([a-zA-Z0-9-_\/]*)}/i', $uri, $matches))) {
                $page = $item->page($locale);
                $date = $page->releasedAt()->format($matches[1]);
                $routeSpecification = $routeSpecification->withUri(\preg_replace('/\${RELEASE:([a-zA-Z0-9-_\/]*)}/i', $date, $uri), $name);
            }
        }



        return $routeSpecification;
    }
}
