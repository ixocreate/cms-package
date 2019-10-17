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
use Ixocreate\Cms\Entity\RouteMatch;
use Ixocreate\Cms\Repository\RouteMatchRepository;
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
     * @var RouteMatchRepository
     */
    private $routeMatchRepository;

    /**
     * CmsRouter constructor.
     * @param MiddlewareFactory $middlewareFactory
     * @param ApplicationUri $applicationUri
     * @param CacheManager $cacheManager
     * @param CompiledGeneratorRoutesCacheable $compiledGeneratorRoutesCacheable
     * @param CompiledMatcherRoutesCacheable $compiledMatcherRoutesCacheable
     */
    public function __construct(
        MiddlewareFactory $middlewareFactory,
        ApplicationUri $applicationUri,
        CacheManager $cacheManager,
        CompiledGeneratorRoutesCacheable $compiledGeneratorRoutesCacheable,
        CompiledMatcherRoutesCacheable $compiledMatcherRoutesCacheable,
        RouteMatchRepository $routeMatchRepository
    ) {
        $this->middlewareFactory = $middlewareFactory;
        $this->applicationUri = $applicationUri;
        $this->cacheManager = $cacheManager;
        $this->compiledGeneratorRoutesCacheable = $compiledGeneratorRoutesCacheable;
        $this->compiledMatcherRoutesCacheable = $compiledMatcherRoutesCacheable;
        $this->routeMatchRepository = $routeMatchRepository;
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

        $uri = (string) $request->getUri()->withQuery('');
        if ($request->getUri()->getPath() === '/') {
            $uri = (string) $request->getUri()->withPath('');
        }

        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->routeMatchRepository->find($uri);

        if ($routeMatch === null) {
            $routes = $this->cacheManager->fetch($this->compiledMatcherRoutesCacheable);
            $matcher = new CompiledUrlMatcher($routes, $context);

            try {
                $routeMatchData = $matcher->match($request->getUri()->getPath());
            } catch (ResourceNotFoundException $e) {
                return RouteResult::fromRouteFailure(Route::HTTP_METHOD_ANY);
            }
        } else {
            $routeMatchData = [
                'middleware' => $routeMatch->middleware(),
                'pageId' => $routeMatch->pageId(),
                '_route' => 'page.' . $routeMatch->pageId(),
            ];
        }

        $route = new Route(
            $request->getUri()->getPath(),
            $this->middlewareFactory->pipeline($routeMatchData['middleware']),
            Route::HTTP_METHOD_ANY,
            $routeMatchData['_route']
        );
        $route->setOptions([
            'pageId' => $routeMatchData['pageId']
        ]);
        unset($routeMatchData['_route'], $routeMatchData['middleware']);

        return RouteResult::fromRoute($route, $routeMatchData);
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
        $type = '*';
        $parts = \explode('.', $name);
        if (\count($parts) == 2) {
            $pageId = $parts[1];
        } else {
            $type = $parts[1];
            $pageId = $parts[2];
        }

        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->routeMatchRepository->findOneBy(['pageId' => $pageId, 'type' => $type]);
        if ($routeMatch === null) {
            $path = $this->generator()->generate($name, $substitutions, UrlGenerator::ABSOLUTE_URL);
        } else {
            $path = $routeMatch->url();
        }

        return (string) $path;
    }
}
