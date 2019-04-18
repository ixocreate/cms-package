<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Middleware\Factory;

use Ixocreate\Application\Http\Middleware\MiddlewareSubManager;
use Ixocreate\Cms\Package\Middleware\CmsMiddleware;
use Ixocreate\Cms\Package\Middleware\DefaultLocaleMiddleware;
use Ixocreate\Cms\Package\Middleware\OldUrlRedirectMiddleware;
use Ixocreate\Cms\Package\Router\CmsRouter;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\RouteMiddleware;
use Zend\Stratigility\MiddlewarePipe;

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
