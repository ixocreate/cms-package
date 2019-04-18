<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Middleware;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Package\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Package\Cms\Entity\Page;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LoadSitemapMiddleware implements MiddlewareInterface
{
    /**
     * @var SitemapCacheable
     */
    private $sitemapCacheable;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * LoadSitemapMiddleware constructor.
     * @param SitemapCacheable $sitemapCacheable
     * @param CacheManager $cacheManager
     */
    public function __construct(SitemapCacheable $sitemapCacheable, CacheManager $cacheManager)
    {
        $this->sitemapCacheable = $sitemapCacheable;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Page $page */
        $page = $request->getPage();

        $cacheable = $this->sitemapCacheable->withSitemapId((string) $page->sitemapId());

        $sitemap = $this->cacheManager->fetch($cacheable);

        $request = $request->withSitemap($sitemap);

        return $handler->handle($request);
    }
}
