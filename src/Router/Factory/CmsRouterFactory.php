<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router\Factory;

use Ixocreate\Application\Http\Middleware\MiddlewareSubManager;
use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Cache\CacheableSubManager;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\CompiledGeneratorRoutesCacheable;
use Ixocreate\Cms\Cacheable\CompiledMatcherRoutesCacheable;
use Ixocreate\Cms\Repository\RouteMatchRepository;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Database\Repository\Factory\RepositorySubManager;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;

final class CmsRouterFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CmsRouter|mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($container->get(MiddlewareSubManager::class)));
        return new CmsRouter(
            $middlewareFactory,
            $container->get(ApplicationUri::class),
            $container->get(CacheManager::class),
            $container->get(CacheableSubManager::class)->get(CompiledGeneratorRoutesCacheable::class),
            $container->get(CacheableSubManager::class)->get(CompiledMatcherRoutesCacheable::class),
            $container->get(RepositorySubManager::class)->get(RouteMatchRepository::class)
        );
    }
}
