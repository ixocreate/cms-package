<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router\Factory;

use Ixocreate\ApplicationHttp\Middleware\MiddlewareSubManager;
use Ixocreate\Cms\Action\Frontend\RenderAction;
use Ixocreate\Cms\Config\Config;
use Ixocreate\Cms\Middleware\LoadPageContentMiddleware;
use Ixocreate\Cms\Middleware\LoadPageMiddleware;
use Ixocreate\Cms\Middleware\LoadPageTypeMiddleware;
use Ixocreate\Cms\Middleware\LoadSitemapMiddleware;
use Ixocreate\Cms\Middleware\NotFoundMiddleware;
use Ixocreate\Cms\PageType\MiddlewarePageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\PageType\RoutingAwareInterface;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Contract\ServiceManager\FactoryInterface;
use Ixocreate\Contract\ServiceManager\ServiceManagerInterface;
use Ixocreate\Database\Repository\Factory\RepositorySubManager;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\ProjectUri\ProjectUri;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;

final class CmsRouterFactory implements FactoryInterface
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var ProjectUri
     */
    private $projectUri;

    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var MiddlewareSubManager
     */
    private $middlewareSubManager;

    /**
     * @var MiddlewareFactory
     */
    private $middlewareFactory;

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        /** @var Config $cmsConfig */
        $cmsConfig = $container->get(Config::class);

        /** @var LocaleManager $localeManager */
        $this->localeManager = $container->get(LocaleManager::class);

        /** @var ProjectUri projectUri */
        $this->projectUri = $container->get(ProjectUri::class);

        /** @var PageTypeSubManager pageTypeSubManager */
        $this->pageTypeSubManager = $container->get(PageTypeSubManager::class);

        $this->middlewareSubManager = $container->get(MiddlewareSubManager::class);
        $this->middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($this->middlewareSubManager));

        /** @var PageRepository $pageRepository */
        $pageRepository = $container->get(RepositorySubManager::class)->get(PageRepository::class);

        $routeCollection = new RouteCollection();

        $tree = $pageRepository->fetchTree();
        foreach ($this->localeManager->all() as $locale) {
            $routes = [];

            $lang = \Locale::getPrimaryLanguage($locale['locale']);
            $region = \Locale::getRegion($locale['locale']);

            $this->parseTree($tree, $routes, $locale['locale'], $lang, $region);
            $routes = \array_reverse($routes);

            foreach ($routes as $item) {

                $routeObj = new Route($item['path']);
                if (!empty($item['uri'])) {
                    $routeObj->setHost($item['uri']->getHost());
                }

                $routeObj->setDefault('pageId', $item['id']);
                $routeObj->setDefault('locale', $locale['locale']);
                $routeObj->setDefault('middleware', $item['middleware']);

                $routName = 'page.' . $item['id'];
                if ($item['notFoundRoot']) {
                    $routName .= '.notFound';
                    $routeObj->setRequirement('wildcard', '.*');
                    $routeObj->setDefault('notFound', true);
                }
                $routeCollection->add($routName, $routeObj);
            }

            foreach ($routeCollection->all() as $route) {
              //  var_dump($route);
            }
        }

        return new CmsRouter($routeCollection, $cmsConfig, $this->localeManager, $this->projectUri);
    }

    /**
     * @param array $tree
     * @param $routes
     * @param string $locale
     * @param string $path
     * @param null|UriInterface $uri
     */
    private function parseTree(array $tree, &$routes, string $locale, $lang, $region, string $path = '', ?UriInterface $uri = null): void
    {
        $middleware = [
            LoadPageMiddleware::class,
            LoadSitemapMiddleware::class,
            LoadPageTypeMiddleware::class,
            LoadPageContentMiddleware::class,
        ];

        foreach ($tree as $item) {
            if (empty($item['pages'][$locale])) {
                continue;
            }

            /** @var PageTypeInterface $pageType */
            $pageType = $this->pageTypeSubManager->get($item['sitemap']->pageType());


            if ($pageType instanceof MiddlewarePageTypeInterface) {
                $middleware = \array_merge($middleware, \array_values($pageType->middleware()));
            }

            $middleware[] = RenderAction::class;

            if ($pageType instanceof RoutingAwareInterface) {
                $routing = \ltrim($pageType->routing(), '/');

                if (\preg_match('/\${URI:([a-z0-9-_]*)}/i', $routing, $matches) !== false) {
                    $uri = $this->projectUri->getAlternativeUri($matches[1]);
                    $routing = \preg_replace('/\${URI:([a-z0-9-_]*)}/i', '', $routing);
                }
            } else {
                if ($pageType instanceof RootPageTypeInterface) {
                    $routing = '/';
                } else {
                    $routing = '${PARENT}/${SLUG}';
                }
            }

            $currentPath = \str_replace('${PARENT}', \rtrim($path, '/'), $routing);

            if (empty($item['pages'][$locale]->slug()) && \mb_strpos($routing, '${SLUG}') !== false) {
                continue;
            }

            if (!empty($item['pages'][$locale]->slug())) {
                $currentPath = \str_replace('${SLUG}', $item['pages'][$locale]->slug(), $currentPath);
            }

            if ($pageType instanceof RoutingAwareInterface) {
                $childrenPath = $currentPath;
                if (\mb_strpos($routing, '${LANG}')) {
                    $childrenPath = \str_replace('${LANG}', $lang, $childrenPath);
                    if ($this->localeManager->defaultLocale() === $locale) {
                        $currentPath = \rtrim(\str_replace('${LANG}', '', $currentPath), '/');
                    } else {
                        $currentPath = $childrenPath;
                    }
                }
                if (\mb_strpos($routing, '${REGION}')) {
                    $childrenPath = \str_replace('${REGION}', $lang, $childrenPath);
                    if ($this->localeManager->defaultLocale() === $locale) {
                        $currentPath = \rtrim(\str_replace('${REGION}', '', $currentPath), '/');
                    } else {
                        $currentPath = $childrenPath;
                    }
                }
            } else {
                $childrenPath = $currentPath;
            }

            if ($pageType instanceof RootPageTypeInterface) {
//                $routes[] = [
//                    'path' => $currentPath . '/{wildcard}',
//                    'uri' => $uri,
//                    'id' => (string) $item['pages'][$locale]->id(),
//                    'middleware' => $this->middlewareSubManager->get(NotFoundMiddleware::class),
//                    'notFoundRoot' => true,
//                ];
            }

            $routes[] = [
                'path' => $currentPath,
                'uri' => $uri,
                'id' => (string) $item['pages'][$locale]->id(),
                'middleware' => $this->middlewareFactory->pipeline($middleware),
                'notFoundRoot' => false,
            ];

            if (!empty($item['children'])) {
                $this->parseTree($item['children'], $routes, $locale, $lang, $region, $childrenPath, $uri);
            }
        }
    }
}
