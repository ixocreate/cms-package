<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Router\Factory;

use Ixocreate\Application\Http\Middleware\MiddlewareSubManager;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Package\Cms\Cacheable\RouteCollectionCacheable;
use Ixocreate\Package\Cms\Router\CmsRouter;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Ixocreate\Package\Cache\CacheableSubManager;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;

final class CmsRouterFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CmsRouter|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $routeCollection = $container
            ->get(CacheManager::class)
            ->fetch($container->get(CacheableSubManager::class)->get(RouteCollectionCacheable::class));

        $middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($container->get(MiddlewareSubManager::class)));

        return new CmsRouter($routeCollection, $middlewareFactory);
    }
}
