<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Middleware;

use Ixocreate\ApplicationHttp\ErrorHandling\Response\NotFoundHandler;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Entity\Page;
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
     * @var PageCacheable
     */
    private $pageCacheable;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * LoadPageMiddleware constructor.
     * @param PageCacheable $pageCacheable
     * @param CacheManager $cacheManager
     * @param LocaleManager $localeManager
     * @param NotFoundHandler $notFoundHandler
     */
    public function __construct(PageCacheable $pageCacheable, CacheManager $cacheManager, LocaleManager $localeManager, NotFoundHandler $notFoundHandler)
    {
        $this->localeManager = $localeManager;
        $this->notFoundHandler = $notFoundHandler;
        $this->pageCacheable = $pageCacheable;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        $pageId = $routeResult->getMatchedRoute()->getOptions()['pageId'];

        $cacheable = $this->pageCacheable->withPageId($pageId);
        /** @var Page $page */
        $page = $this->cacheManager->fetch($cacheable);

        if (!($page instanceof Page)) {
            return $this->notFoundHandler->process($request, $handler);
        }

        if (!$page->isOnline()) {
            return $this->notFoundHandler->process($request, $handler);
        }

        $this->localeManager->acceptLocale($page->locale());

        $request = $request->withPage($page);

        return $handler->handle($request);
    }
}
