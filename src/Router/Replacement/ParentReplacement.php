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

final class ParentReplacement implements ReplacementInterface
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
     * @param RoutingItem $routingItem
     * @throws \Exception
     * @return void
     */
    public function replace(
        RouteSpecification $routeSpecification,
        string $locale,
        RoutingItem $routingItem
    ): void {
        $check = false;
        foreach ($routeSpecification->uris() as $name => $uri) {
            if (\strpos($uri, '${PARENT}') === false) {
                continue;
            }

            $check = true;
            break;
        }

        if ($check === false) {
            return;
        }

        foreach ($routeSpecification->uris() as $name => $uri) {
            $routeSpecification->addUri(\str_replace('${PARENT}', '', $uri), $name);
        }

        if (empty($routingItem->parent())) {
            return;
        }

        $parentRouting = $routingItem->parent()->pageRoute($locale);
        if (empty($parentRouting)) {
            return;
        }

        $parentRouting->addChild($routeSpecification);
    }
}
