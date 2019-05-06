<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Middleware;

use Ixocreate\Application\Http\ErrorHandling\Response\NotFoundHandler;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Site\Tree\Container;
use Ixocreate\Cms\Site\Tree\Item;
use Ixocreate\Cms\Site\Tree\Search\ActiveSearch;
use Ixocreate\Cms\Site\Tree\Search\OnlineSearch;
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
     * @var Container
     */
    private $container;
    /**
     * @var NotFoundHandler
     */
    private $notFoundHandler;

    /**
     * LoadSitemapMiddleware constructor.
     * @param SitemapCacheable $sitemapCacheable
     * @param CacheManager $cacheManager
     * @param Container $container
     * @param NotFoundHandler $notFoundHandler
     */
    public function __construct(SitemapCacheable $sitemapCacheable, CacheManager $cacheManager, Container $container, NotFoundHandler $notFoundHandler)
    {
        $this->sitemapCacheable = $sitemapCacheable;
        $this->cacheManager = $cacheManager;
        $this->container = $container;
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

        $cacheable = $this->sitemapCacheable->withSitemapId((string) $page->sitemapId());

        $sitemap = $this->cacheManager->fetch($cacheable);

        $container = $this->container
            ->filter(OnlineSearch::class, ['locale' => $page->locale()])
            ->filter(ActiveSearch::class, ['sitemap' => $sitemap])
            ->flatten();

        $item = $container->find(function (Item $item) use ($sitemap){
            return (string) $item->sitemap()->id() === (string) $sitemap->id();
        });

        if (empty($item)) {
            return $this->notFoundHandler->process($request, $handler);
        }

        $request = $request->withSitemap($sitemap);

        return $handler->handle($request);
    }
}
