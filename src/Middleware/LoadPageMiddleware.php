<?php
namespace KiwiSuite\Cms\Middleware;

use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Intl\LocaleManager;
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

    public function __construct(PageRepository $pageRepository, LocaleManager $localeManager)
    {
        $this->pageRepository = $pageRepository;
        $this->localeManager = $localeManager;
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
        $page = $this->pageRepository->find($pageId);

        $this->localeManager->acceptLocale($page->locale());

        //TODO check page

        $request = $request->withPage($page);

        return $handler->handle($request);
    }
}
