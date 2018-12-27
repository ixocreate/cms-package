<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Middleware;

use Ixocreate\ApplicationHttp\ErrorHandling\Response\NotFoundHandler;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Intl\LocaleManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

final class LoadPageMiddleware implements MiddlewareInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var NotFoundHandler
     */
    private $notFoundHandler;

    public function __construct(PageRepository $pageRepository, LocaleManager $localeManager, NotFoundHandler $notFoundHandler)
    {
        $this->pageRepository = $pageRepository;
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

        /** @var Page $page */
        $page = $this->pageRepository->find($pageId);

        if (!$page->isOnline()) {
            return $this->notFoundHandler->process($request, $handler);
        }

        $this->localeManager->acceptLocale($page->locale());

        //TODO check page

        $request = $request->withPage($page);

        return $handler->handle($request);
    }
}
