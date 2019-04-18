<?php
declare(strict_types=1);
namespace Ixocreate\Package\Cms\Router\Replacement;

use Ixocreate\Package\Cms\Router\RouteSpecification;
use Ixocreate\Package\Cms\Router\RoutingItem;

interface ReplacementInterface
{
    public function priority(): int;

    public function replace(
        RouteSpecification $routeSpecification,
        string $locale,
        RoutingItem $routingItem
    ): RouteSpecification;
}
