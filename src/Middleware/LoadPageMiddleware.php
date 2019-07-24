<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Middleware;

use Ixocreate\Application\Http\ErrorHandling\Response\NotFoundHandler;
use Ixocreate\Cms\Tree\MutationCollection;
use Ixocreate\Cms\Tree\TreeFactory;
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
     * @var TreeFactory
     */
    private $treeFactory;

    /**
     * LoadPageMiddleware constructor.
     * @param LocaleManager $localeManager
     * @param NotFoundHandler $notFoundHandler
     */
    public function __construct(TreeFactory $treeFactory, LocaleManager $localeManager, NotFoundHandler $notFoundHandler)
    {
        $this->treeFactory = $treeFactory;
        $this->localeManager = $localeManager;
        $this->notFoundHandler = $notFoundHandler;
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
        $sitemapId = $routeResult->getMatchedRoute()->getOptions()['sitemapId'];

        try {
            $item = $this->treeFactory->createItem($sitemapId, new MutationCollection());
        } catch (\Exception $e) {
            return $this->notFoundHandler->process($request, $handler);
        }

        if (empty($item)) {
            return $this->notFoundHandler->process($request, $handler);
        }

        if (!$item->hasPageId($pageId)) {
            return $this->notFoundHandler->process($request, $handler);
        }

        $page = $item->pageById($pageId);

        if (!$item->isOnline($page->locale())) {
            return $this->notFoundHandler->process($request, $handler);
        }

        $this->localeManager->acceptLocale($page->locale());

        $request = $request->withPage($page);
        $request = $request->withSitemap($item->sitemap());
        $request = $request->withPageType($item->pageType());
        return $handler->handle($request);
    }
}
