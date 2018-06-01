<?php
namespace KiwiSuite\Cms\Router\Factory;

use KiwiSuite\ApplicationHttp\Middleware\MiddlewareSubManager;
use KiwiSuite\Cms\Action\Frontend\RenderAction;
use KiwiSuite\Cms\Middleware\LoadPageMiddleware;
use KiwiSuite\Cms\Middleware\LoadPageTypeMiddleware;
use KiwiSuite\Cms\Middleware\LoadSitemapMiddleware;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeMapping;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Router\CmsRouter;
use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Database\Repository\Factory\RepositorySubManager;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;
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
     * @var PageTypeMapping
     */
    private $pageTypeMapping;

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $this->pageTypeSubManager = $container->get(PageTypeSubManager::class);
        $this->middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($container->get(MiddlewareSubManager::class)));
        $this->pageTypeMapping = $container->get(PageTypeMapping::class);

        /** @var PageRepository $pageRepository */
        $pageRepository = $container->get(RepositorySubManager::class)->get(PageRepository::class);
        $routes = [];
        $tree = $pageRepository->fetchTree();
        $this->parseTree($tree, $routes, $options['locale']);
        $routes = array_reverse($routes);

        $router = new CmsRouter();
        foreach ($routes as $item) {
            $routeObj = new Route($item['path'], $item['middleware'], Route::HTTP_METHOD_ANY, "page." . $item['id']);
            $routeObj->setOptions([
                'pageId' => $item['id'],
            ]);
            $router->addRoute($routeObj);
        }
        return $router;
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
        ];

        foreach ($tree as $item) {
            if (empty($item['pages'][$locale])) {
                continue;
            }

            /** @var PageTypeInterface $pageType */
            $pageType = $this->pageTypeSubManager->get($this->pageTypeMapping->getMapping()[$item['sitemap']->pageType()]);

            $itemMiddleware = $pageType->middleware();
            if (empty($itemMiddleware)) {
                $itemMiddleware = $middleware;
            } else {
                $itemMiddleware = array_merge($middleware, array_values($itemMiddleware));
            }

            $itemMiddleware[] = RenderAction::class;

            $routing = '/' . ltrim($pageType->routing(), '/');
            $currentPath = $path . $routing;

            if (empty($item['pages'][$locale]->slug()) && strpos($pageType->routing(), '${SLUG}') !== false) {
                continue;
            }

            if (!empty($item['pages'][$locale]->slug())) {
                $currentPath = str_replace('${SLUG}', $item['pages'][$locale]->slug(), $currentPath);
            }

            $routes[] = [
                'path' => $currentPath,
                'id' => (string) $item['pages'][$locale]->id(),
                'middleware' => $this->middlewareFactory->pipeline($itemMiddleware)
            ];


            if (!empty($item['children'])) {
                $this->parseTree($item['children'], $routes, $locale, $currentPath);
            }
        }
    }
}
