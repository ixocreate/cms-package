<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Middleware;


use Ixocreate\Cms\Repository\OldRedirectRepository;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\ProjectUri\ProjectUri;
use mysql_xdevapi\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

class OldUrlRedirectMiddleware implements MiddlewareInterface
{
    /**
     * @var ProjectUri
     */
    private $projectUri;

    /**
     * @var OldRedirectRepository
     */
    private $oldRedirectRepository;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageRoute
     */
    private $pageRoute;

    public function __construct(OldRedirectRepository $oldRedirectRepository, ProjectUri $projectUri, PageRepository $pageRepository, PageRoute $pageRoute)
    {
        $this->oldRedirectRepository = $oldRedirectRepository;
        $this->projectUri = $projectUri;
        $this->pageRepository = $pageRepository;
        $this->pageRoute = $pageRoute;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $oldUri = $request->getUri();
        $oldPage = $this->oldRedirectRepository->findOneBy(['oldUrl' => $oldUri]);
        if (empty($oldPage)){
            return $handler->handle($request);
        }
        $url = $this->pageRepository->findOneBy(['id' => $oldPage->pageId]);
        $newUri = $this->pageRoute->fromPage($url);
        if ($oldPage === null or !$url->isOnline()) {
            return $handler->handle($request);
        }
        return new RedirectResponse($newUri);
    }
}