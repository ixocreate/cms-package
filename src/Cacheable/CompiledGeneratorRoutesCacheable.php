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
use Ixocreate\Cms\Router\Tree\RoutingTreeFactory;
use Ixocreate\Intl\LocaleManager;
use Symfony\Component\Routing\Generator\Dumper\CompiledUrlGeneratorDumper;

final class CompiledGeneratorRoutesCacheable implements CacheableInterface
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var RoutingTreeFactory
     */
    private $routingTreeFactory;

    public function __construct(
        LocaleManager $localeManager,
        RoutingTreeFactory $routingTreeFactory
    ) {
        $this->localeManager = $localeManager;
        $this->routingTreeFactory = $routingTreeFactory;
    }

    /**
     * @return mixed
     */
    public function uncachedResult()
    {
        return (new CompiledUrlGeneratorDumper(
            (new RouteCollection($this->localeManager))
                ->build($this->routingTreeFactory->createRoot())
        ))->getCompiledRoutes();
    }

    /**
     * @return string
     */
    public function cacheName(): string
    {
        return 'cms_store';
    }

    /**
     * @return string
     */
    public function cacheKey(): string
    {
        return 'compiled.url.generator';
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 2592000;
    }
}
