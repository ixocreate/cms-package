<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Router\Replacement;

use Ixocreate\Cms\Package\Router\RouteSpecification;
use Ixocreate\Cms\Package\Router\RoutingItem;

interface ReplacementInterface
{
    public function priority(): int;

    public function replace(
        RouteSpecification $routeSpecification,
        string $locale,
        RoutingItem $routingItem
    ): RouteSpecification;
}
