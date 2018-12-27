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
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Contract\ServiceManager\FactoryInterface;
use Ixocreate\Contract\ServiceManager\ServiceManagerInterface;
use Ixocreate\Database\Repository\Factory\RepositorySubManager;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\ProjectUri\ProjectUri;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route;

final class CmsRouterFactory implements FactoryInterface
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

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

        $this->pageTypeSubManager = $container->get(PageTypeSubManager::class);
        $this->middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($container->get(MiddlewareSubManager::class)));

        /** @var PageRepository $pageRepository */
        $pageRepository = $container->get(RepositorySubManager::class)->get(PageRepository::class);

        $tree = $pageRepository->fetchTree();
        $i18nRouters = [];
        foreach ($container->get(LocaleManager::class)->all() as $locale) {
            $i18nRouters[$locale['locale']] = new FastRouteRouter();
            $routes = [];
            $this->parseTree($tree, $routes, $locale['locale']);
            $routes = \array_reverse($routes);


            foreach ($routes as $item) {
                $routeObj = new Route($item['path'], $item['middleware'], Route::HTTP_METHOD_ANY, "page." . $item['id']);
                $routeObj->setOptions([
                    'pageId' => $item['id'],
                ]);
                $i18nRouters[$locale['locale']]->addRoute($routeObj);
            }
        }

        return new CmsRouter($i18nRouters, $cmsConfig, $container->get(LocaleManager::class), $container->get(ProjectUri::class));
    }

    /**
     * @param array $tree
     * @param $routes
     * @param string $locale
     * @param string $path
     */
    private function parseTree(array $tree, &$routes, string $locale, string $path = ""): void
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

            $itemMiddleware = $pageType->middleware();
            if (empty($itemMiddleware)) {
                $itemMiddleware = $middleware;
            } else {
                $itemMiddleware = \array_merge($middleware, \array_values($itemMiddleware));
            }

            $itemMiddleware[] = RenderAction::class;

            $routing = '/' . \ltrim($pageType->routing(), '/');
            $currentPath = \rtrim($path, '/') . $routing;

            if (empty($item['pages'][$locale]->slug()) && \mb_strpos($pageType->routing(), '${SLUG}') !== false) {
                continue;
            }

            if (!empty($item['pages'][$locale]->slug())) {
                $currentPath = \str_replace('${SLUG}', $item['pages'][$locale]->slug(), $currentPath);
            }

            $routes[] = [
                'path' => $currentPath,
                'id' => (string) $item['pages'][$locale]->id(),
                'middleware' => $this->middlewareFactory->pipeline($itemMiddleware),
            ];


            if (!empty($item['children'])) {
                $this->parseTree($item['children'], $routes, $locale, $currentPath);
            }
        }
    }
}
