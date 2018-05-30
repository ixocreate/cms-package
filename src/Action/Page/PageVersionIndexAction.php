<?php

namespace KiwiSuite\Cms\Action\Page;


use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

class PageVersionIndexAction implements MiddlewareInterface
{

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    public function __construct(PageVersionRepository $pageVersionRepository)
    {
        $this->pageVersionRepository = $pageVersionRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        $result = $this->pageVersionRepository->findBy(['pageId' => $routeResult->getMatchedParams()['id']], ['createdAt' => 'DESC'], 1);

        if (empty($result)) {
            return new ApiSuccessResponse([]);
        }

        /** @var PageVersion $pageVersion */
        $pageVersion = current($result);

        $content = $pageVersion->content();
        if (empty($content)) {
            $content = [];
        }

        return new ApiSuccessResponse($content);
    }
}
