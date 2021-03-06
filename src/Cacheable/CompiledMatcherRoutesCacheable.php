<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Cacheable;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Cms\Router\RouteCollection;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;

final class CompiledMatcherRoutesCacheable implements CacheableInterface
{
    /**
     * @var RouteCollection
     */
    private $ixocreateRouteCollection;

    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    private $routeCollection;

    public function __construct(RouteCollection $routeCollection)
    {
        $this->ixocreateRouteCollection = $routeCollection;
    }

    public function withRouteCollection(\Symfony\Component\Routing\RouteCollection $routeCollection): CompiledMatcherRoutesCacheable
    {
        $cachable = clone $this;
        $cachable->routeCollection = $routeCollection;

        return $cachable;
    }

    /**
     * @inheritDoc
     */
    public function uncachedResult()
    {
        if (empty($this->routeCollection)) {
            $this->routeCollection = $this->ixocreateRouteCollection->build();
        }

        return (new CompiledUrlMatcherDumper($this->routeCollection))->getCompiledRoutes();
    }

    /**
     * @inheritDoc
     */
    public function cacheName(): string
    {
        return 'cms_store';
    }

    /**
     * @inheritDoc
     */
    public function cacheKey(): string
    {
        return 'compiled.url.matcher';
    }

    /**
     * @inheritDoc
     */
    public function cacheTtl(): int
    {
        return 2592000;
    }
}
