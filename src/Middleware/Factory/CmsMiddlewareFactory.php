<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Middleware\Factory;

use Ixocreate\Application\Http\Middleware\MiddlewareSubManager;
use Ixocreate\Cms\Middleware\CmsMiddleware;
use Ixocreate\Cms\Middleware\DefaultLocaleMiddleware;
use Ixocreate\Cms\Middleware\OldUrlRedirectMiddleware;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Laminas\Stratigility\MiddlewarePipe;
use Mezzio\MiddlewareContainer;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;

final class CmsMiddlewareFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $middlewarePipe = new MiddlewarePipe();
        $middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($container->get(MiddlewareSubManager::class)));

        //$middlewarePipe->pipe($middlewareFactory->lazy(DefaultLocaleMiddleware::class));
        $middlewarePipe->pipe(new RouteMiddleware($container->get(CmsRouter::class)));
        $middlewarePipe->pipe($middlewareFactory->lazy(DispatchMiddleware::class));
        $middlewarePipe->pipe($middlewareFactory->lazy(OldUrlRedirectMiddleware::class));

        return new CmsMiddleware($middlewarePipe);
    }
}
