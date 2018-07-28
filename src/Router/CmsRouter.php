<?php
/**
 * kiwi-suite/cms (https://github.com/kiwi-suite/cms)
 *
 * @package kiwi-suite/cms
 * @see https://github.com/kiwi-suite/cms
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace KiwiSuite\Cms\Router;

use KiwiSuite\ApplicationHttp\Request\RequestWrapperInterface;
use KiwiSuite\Cms\Config\Config;
use KiwiSuite\Intl\LocaleManager;
use KiwiSuite\ProjectUri\ProjectUri;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\Exception;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

final class CmsRouter implements RouterInterface
{
    /**
     * @var FastRouteRouter[]
     */
    private $routers;
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
     * @param array $routers
     * @param Config $config
     * @param LocaleManager $localeManager
     * @param ProjectUri $projectUri
     */
    public function __construct(array $routers, Config $config, LocaleManager $localeManager, ProjectUri $projectUri)
    {
        $this->routers = $routers;
        $this->config = $config;
        $this->localeManager = $localeManager;
        $this->projectUri = $projectUri;
    }

    private function getLocaleRouter(string $locale): FastRouteRouter
    {
        if (!array_key_exists($locale, $this->routers)) {
            throw new \Exception(sprintf("invalid locale %s", $locale));
        }

        return $this->routers[$locale];
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
        foreach (array_keys($this->routers) as $locale) {
            $requestUri = $request->getUri();
            if ($request instanceof RequestWrapperInterface) {
                $requestUri = $request->rootRequest()->getUri();
            }
            $localizationUri = $this->getLocalizationBaseUrl($locale);

            if (stripos((string) $requestUri, (string) $localizationUri) === false) {
                continue;
            }

            $path = substr($requestUri->getPath(), strlen($localizationUri->getPath()));

            return $this->getLocaleRouter($locale)->match($request->withUri($requestUri->withPath($path)));
        }

        if ($this->config->defaultBaseUrl() !== null) {
            $requestUri = $request->getUri();
            if ($request instanceof RequestWrapperInterface) {
                $requestUri = $request->rootRequest()->getUri();
            }

            if (rtrim((string) $requestUri, '/') === rtrim((string) $this->getDefaultBaseUrl(), '/')) {
                $defaultLocale = $this->localeManager->defaultLocale();
                return $this->getLocaleRouter($defaultLocale)->match($request->withUri($requestUri->withPath("/")));
            }

        }

        return RouteResult::fromRouteFailure(Route::HTTP_METHOD_ANY);
    }

    /**
     * Generate a URI from the named route.
     *
     * Takes the named route and any substitutions, and attempts to generate a
     * URI from it. Additional router-dependent options may be passed.
     *
     * The URI generated MUST NOT be escaped. If you wish to escape any part of
     * the URI, this should be performed afterwards; consider passing the URI
     * to league/uri to encode it.
     *
     * @see https://github.com/auraphp/Aura.Router/blob/3.x/docs/generating-paths.md
     * @see https://docs.zendframework.com/zend-router/routing/
     * @throws Exception\RuntimeException if unable to generate the given URI.
     */
    public function generateUri(string $name, array $substitutions = [], array $options = []): string
    {
        if (!array_key_exists('locale', $options)) {
            throw new \Exception("Invalid locale");
        }
        $locale = $options['locale'];
        $path = $this->getLocaleRouter($locale)->generateUri($name, $substitutions, $options);

        $uri = $this->getLocalizationBaseUrl($locale, true);
        $uri = $uri->withPath(rtrim($uri->getPath(), '/') . $path);

        return (string) $uri;
    }

    /**
     * @param string $locale
     * @return Uri
     */
    private function getLocalizationBaseUrl(string $locale): Uri
    {
        $uriString = strtr(
            $this->config->localizationUrlSchema(),
            [
                '%MAIN_URL%' => rtrim((string) $this->projectUri->getMainUrl(), '/'),
                '%LANG%' => \Locale::getPrimaryLanguage($locale),
                '%REGION%' => \Locale::getRegion($locale),
            ]
        );

        return new Uri($uriString);
    }

    private function getDefaultBaseUrl(): Uri
    {
        $uriString = strtr(
            $this->config->defaultBaseUrl(),
            [
                '%MAIN_URL%' => rtrim((string) $this->projectUri->getMainUrl(), '/'),
            ]
        );

        return new Uri($uriString);
    }
}
