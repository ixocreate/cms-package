<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router;

use Ixocreate\Application\Uri\ApplicationUri;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

final class CmsRouter implements RouterInterface
{
    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var UrlGenerator
     */
    private $generator;

    /**
     * @var MiddlewareFactory
     */
    private $middlewareFactory;

    /**
     * CmsRouter constructor.
     * @param RouteCollection $routes
     * @param MiddlewareFactory $middlewareFactory
     * @param ApplicationUri $applicationUri
     */
    public function __construct(RouteCollection $routes, MiddlewareFactory $middlewareFactory, ApplicationUri $applicationUri)
    {
        $this->routes = $routes;
        $context = new RequestContext('', 'GET', $applicationUri->getMainUri()->getHost(), $applicationUri->getMainUri()->getScheme());
        $this->generator = new UrlGenerator($this->routes, $context);

        $this->middlewareFactory = $middlewareFactory;
    }

    /**
     * @param Route $route
     */
    public function addRoute(Route $route): void
    {
        throw new \BadMethodCallException("Cant set routes");
    }

    /**
     * Match a request against the known routes.
     *
     * Implementations will aggregate required information from the provided
     * request instance, and pass them to the underlying router implementation;
     * when done, they will then marshal a `RouteResult` instance indicating
     * the results of the matching operation and return it to the caller.
     */
    public function match(Request $request): RouteResult
    {
        $context = new RequestContext(
            '',
            $request->getMethod(),
            $request->getUri()->getHost(),
            $request->getUri()->getScheme()
        );

        $matcher = new UrlMatcher($this->routes, $context);

        try {
            $routeMatch = $matcher->match($request->getUri()->getPath());
        } catch (ResourceNotFoundException $e) {
            return RouteResult::fromRouteFailure(Route::HTTP_METHOD_ANY);
        }

        $route = new Route(
            $this->routes->get($routeMatch['_route'])->getPath(),
            $this->middlewareFactory->pipeline($routeMatch['middleware']),
            Route::HTTP_METHOD_ANY,
            $routeMatch['_route']
        );
        $route->setOptions(['pageId' => $routeMatch['pageId']]);
        unset($routeMatch['_route'], $routeMatch['middleware']);

        return RouteResult::fromRoute($route, $routeMatch);
    }

    /**
     * @param string $name
     * @param array $substitutions
     * @param array $options
     * @throws \Exception
     * @return string
     */
    public function generateUri(string $name, array $substitutions = [], array $options = []): string
    {
        $path = $this->generator->generate($name, $substitutions, UrlGenerator::ABSOLUTE_URL);
        return (string) $path;
    }
}
