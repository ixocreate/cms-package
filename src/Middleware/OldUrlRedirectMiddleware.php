<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Middleware;

use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Cms\Repository\OldRedirectRepository;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Router\PageRoute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class OldUrlRedirectMiddleware implements MiddlewareInterface
{
    /**
     * @var ApplicationUri
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

    public function __construct(OldRedirectRepository $oldRedirectRepository, ApplicationUri $projectUri, PageRepository $pageRepository, PageRoute $pageRoute)
    {
        $this->oldRedirectRepository = $oldRedirectRepository;
        $this->projectUri = $projectUri;
        $this->pageRepository = $pageRepository;
        $this->pageRoute = $pageRoute;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $oldUri = $request->getUri()->withQuery('');
        $oldPage = $this->oldRedirectRepository->findOneBy(['oldUrl' => $oldUri]);
        if (empty($oldPage)) {
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
