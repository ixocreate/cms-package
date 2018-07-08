<?php

namespace KiwiSuite\Cms\Action\Page;


use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiListResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Contract\Resource\AdminAwareInterface;
use KiwiSuite\Contract\Resource\ResourceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexAction implements MiddlewareInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var AdminAwareInterface $resource */
        $resource = $request->getAttribute(ResourceInterface::class);

        return new ApiListResponse($resource, $this->pageRepository->fetchTree(), $resource->listSchema(), []);
    }
}
