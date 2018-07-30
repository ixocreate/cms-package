<?php
declare(strict_types=1);

namespace KiwiSuite\Cms\Middleware\Factory;

use KiwiSuite\ApplicationHttp\Middleware\MiddlewareSubManager;
use KiwiSuite\Cms\Middleware\CmsMiddleware;
use KiwiSuite\Cms\Router\CmsRouter;
use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
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

        $middlewarePipe->pipe(new RouteMiddleware($container->get(CmsRouter::class)));
        $middlewarePipe->pipe($middlewareFactory->lazy(DispatchMiddleware::class));

        return new CmsMiddleware($middlewarePipe);
    }
}
