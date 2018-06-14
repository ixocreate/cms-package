<?php
namespace KiwiSuite\Cms\Middleware;

use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LoadPageTypeMiddleware implements MiddlewareInterface
{

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * LoadSitemapMiddleware constructor.
     * @param PageTypeSubManager $pageTypeSubManager
     */
    public function __construct(PageTypeSubManager $pageTypeSubManager)
    {
        $this->pageTypeSubManager = $pageTypeSubManager;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Sitemap $sitemap */
        $sitemap = $request->getAttribute(Sitemap::class);

        $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

        $request = $request->withAttribute(PageTypeInterface::class, $pageType);

        return $handler->handle($request);
    }
}
