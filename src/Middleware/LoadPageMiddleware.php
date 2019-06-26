<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Middleware;

use Ixocreate\Application\Http\ErrorHandling\Response\NotFoundHandler;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\StructureCacheable;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Tree\Container;
use Ixocreate\Cms\Tree\Factory;
use Ixocreate\Cms\Tree\Item;
use Ixocreate\Cms\Tree\Structure\StructureItem;
use Ixocreate\Intl\LocaleManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

final class LoadPageMiddleware implements MiddlewareInterface
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var NotFoundHandler
     */
    private $notFoundHandler;
    /**
     * @var Container
     */
    private $container;
    /**
     * @var Factory
     */
    private $factory;
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var StructureCacheable
     */
    private $structureCacheable;

    /**
     * LoadPageMiddleware constructor.
     * @param LocaleManager $localeManager
     * @param NotFoundHandler $notFoundHandler
     * @param Container $container
     */
    public function __construct(
        LocaleManager $localeManager,
        NotFoundHandler $notFoundHandler,
        Container $container
    ) {
        $this->localeManager = $localeManager;
        $this->notFoundHandler = $notFoundHandler;
        $this->container = $container;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        $locale = $routeResult->getMatchedRoute()->getOptions()['locale'];
        if (!$this->localeManager->has($locale)) {
            return $this->notFoundHandler->process($request, $handler);
        }
        $structureKey = $routeResult->getMatchedRoute()->getOptions()['structureKey'];

        try {
            /** @var StructureItem $structureItem */
            $structureItem = $this->container->structure()->structureStore()->item($structureKey);
        } catch (\Throwable $e) {
            return $this->notFoundHandler->process($request, $handler);
        }

        $item = $this->container->factory()->createItem($structureItem);

        if (!$item->hasPage($locale)) {
            return $this->notFoundHandler->process($request, $handler);
        }

        if (!$item->isOnline($locale)) {
            return $this->notFoundHandler->process($request, $handler);
        }

        $this->localeManager->acceptLocale($locale);

        $request = $request->withPage($item->page($locale))
            ->withSitemap($item->sitemap())
            ->withPageType($item->pageType());

        return $handler->handle($request);
    }
}
