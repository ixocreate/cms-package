<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Middleware;

use Ixocreate\Application\Http\ErrorHandling\Response\NotFoundHandler;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Package\Cms\Cacheable\PageVersionCacheable;
use Ixocreate\Package\Cms\Entity\Page;
use Ixocreate\Package\Cms\Entity\PageVersion;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LoadPageContentMiddleware implements MiddlewareInterface
{
    /**
     * @var PageVersionCacheable
     */
    private $pageVersionCacheable;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var NotFoundHandler
     */
    private $notFoundHandler;

    public function __construct(PageVersionCacheable $pageVersionCacheable, CacheManager $cacheManager, NotFoundHandler $notFoundHandler)
    {
        $this->pageVersionCacheable = $pageVersionCacheable;
        $this->cacheManager = $cacheManager;
        $this->notFoundHandler = $notFoundHandler;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Page $page */
        $page = $request->getPage();

        $cacheable = $this->pageVersionCacheable->withPageId((string) $page->id());
        $pageVersion = $this->cacheManager->fetch($cacheable);

        if (!($pageVersion instanceof PageVersion)) {
            return $this->notFoundHandler->process($request, $handler);
        }


        $request = $request->withPageVersion($pageVersion);

        return $handler->handle($request);
    }
}
