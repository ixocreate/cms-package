<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router;

use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\CompiledGeneratorRoutesCacheable;
use Ixocreate\Cms\Cacheable\CompiledMatcherRoutesCacheable;
use Ixocreate\Cms\Entity\Page;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\CompiledUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
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
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var MiddlewareFactory
     */
    private $middlewareFactory;
    /**
     * @var ApplicationUri
     */
    private $applicationUri;
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var CompiledGeneratorRoutesCacheable
     */
    private $compiledGeneratorRoutesCacheable;
    /**
     * @var CompiledMatcherRoutesCacheable
     */
    private $compiledMatcherRoutesCacheable;

    /**
     * CmsRouter constructor.
     * @param MiddlewareFactory $middlewareFactory
     * @param ApplicationUri $applicationUri
     * @param CacheManager $cacheManager
     * @param CompiledGeneratorRoutesCacheable $compiledGeneratorRoutesCacheable
     * @param CompiledMatcherRoutesCacheable $compiledMatcherRoutesCacheable
     */
    public function __construct(MiddlewareFactory $middlewareFactory,
        ApplicationUri $applicationUri,
        CacheManager $cacheManager,
        CompiledGeneratorRoutesCacheable $compiledGeneratorRoutesCacheable,
        CompiledMatcherRoutesCacheable $compiledMatcherRoutesCacheable
    ) {
        $this->middlewareFactory = $middlewareFactory;
        $this->applicationUri = $applicationUri;
        $this->cacheManager = $cacheManager;
        $this->compiledGeneratorRoutesCacheable = $compiledGeneratorRoutesCacheable;
        $this->compiledMatcherRoutesCacheable = $compiledMatcherRoutesCacheable;
    }

    private function generator(): UrlGeneratorInterface
    {
        if ($this->generator === null) {
            $context = new RequestContext('', 'GET', $this->applicationUri->getMainUri()->getHost(), $this->applicationUri->getMainUri()->getScheme());
            $routes = $this->cacheManager->fetch($this->compiledGeneratorRoutesCacheable);
            $this->generator = new CompiledUrlGenerator($routes, $context);
        }

        return $this->generator;
    }
    /**
     * @param Route $route
     */
    public function addRoute(Route $route): void
    {
        throw new \BadMethodCallException("Cant set routes");
    }

    /**
     * @param Request $request
     * @return RouteResult
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function match(Request $request): RouteResult
    {
        $context = new RequestContext(
            '',
            $request->getMethod(),
            $request->getUri()->getHost(),
            $request->getUri()->getScheme()
        );

        $routes = $this->cacheManager->fetch($this->compiledMatcherRoutesCacheable);
        $matcher = new CompiledUrlMatcher($routes, $context);

        try {
            $routeMatch = $matcher->match($request->getUri()->getPath());
        } catch (ResourceNotFoundException $e) {
            return RouteResult::fromRouteFailure(Route::HTTP_METHOD_ANY);
        }

        $route = new Route(
            $request->getUri()->getPath(),
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
        $path = $this->generator()->generate($name, $substitutions, UrlGenerator::ABSOLUTE_URL);
        return (string) $path;
    }

    /**
     * @param Page $page
     * @param array $params
     * @param string $routePrefix
     * @return string
     * @throws \Exception
     */
    public function fromPage(Page $page, array $params = [], string $routePrefix = ''): string
    {
        if ($routePrefix !== '') {
            $routePrefix .= '.';
        }
        return $this->generateUri('page.' . $routePrefix . (string)$page->id(), $params);
    }
}
