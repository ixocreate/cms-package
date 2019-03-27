<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router;

use Ixocreate\Cms\Config\Config;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\ProjectUri\ProjectUri;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Zend\Diactoros\Uri;
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
     * @var Config
     */
    private $config;

    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var ProjectUri
     */
    private $projectUri;

    /**
     * CmsRouter constructor.
     * @param array $routes
     * @param Config $config
     * @param LocaleManager $localeManager
     * @param ProjectUri $projectUri
     */
    public function __construct(RouteCollection $routes, Config $config, LocaleManager $localeManager, ProjectUri $projectUri)
    {
        $this->routes = $routes;
        $this->config = $config;
        $this->localeManager = $localeManager;
        $this->projectUri = $projectUri;
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
        //var_dump($routeMatch);die();

        $route = new Route($this->routes->get($routeMatch['_route'])->getPath(), $routeMatch['middleware'], Route::HTTP_METHOD_ANY, $routeMatch['_route']);
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
        if (!\array_key_exists('locale', $options)) {
            throw new \Exception("Invalid locale");
        }
        $locale = $options['locale'];

        $context = new RequestContext('');

        $generator = new UrlGenerator($this->routes, $context);

        $path = $generator->generate($name, $substitutions);

        //$uri = $this->getLocalizationBaseUrl($locale);
        //$uri = $uri->withPath(\rtrim($uri->getPath(), '/') . $path);

        return (string) $path;
    }
}
