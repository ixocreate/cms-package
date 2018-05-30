<?php
declare(strict_types=1);

namespace KiwiSuite\Cms\Middleware\Factory;

use KiwiSuite\ApplicationHttp\Middleware\MiddlewareSubManager;
use KiwiSuite\Cms\Middleware\CmsMiddleware;
use KiwiSuite\Cms\Router\CmsRouter;
use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Intl\LocaleManager;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Uri;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\RouteMiddleware;
use Zend\Stratigility\Middleware\CallableMiddlewareDecorator;
use Zend\Stratigility\Middleware\PathMiddlewareDecorator;
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
        $cmsMiddleware = new CmsMiddleware();
        $middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($container->get(MiddlewareSubManager::class)));

        foreach ($container->get(LocaleManager::class)->all() as $localeItem) {
            $cmsMiddleware->pipe(new CallableMiddlewareDecorator(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($container, $localeItem, $middlewareFactory) {
                //TODO path
                $path = '/de';

                $uri = new Uri($path);
                if (!empty($uri->getScheme()) && $uri->getScheme() !== $request->getUri()->getScheme()) {
                    return $handler->handle($request);
                }

                if (!empty($uri->getHost()) && $uri->getHost() !== $request->getUri()->getHost()) {
                    return $handler->handle($request);
                }

                if (!empty($uri->getPort()) && $uri->getPort() !== $request->getUri()->getPort()) {
                    return $handler->handle($request);
                }

                $middlewarePipe = new MiddlewarePipe();
                $middlewarePipe->pipe(new RouteMiddleware($container->build(CmsRouter::class, ['locale' => $localeItem['locale']])));
                $middlewarePipe->pipe($middlewareFactory->lazy(DispatchMiddleware::class));

                $pathMiddlewareDecorator = new PathMiddlewareDecorator($uri->getPath(), $middlewarePipe);
                return $pathMiddlewareDecorator->process($request, $handler);
            }));
        }

        return $cmsMiddleware;
    }
}