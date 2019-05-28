<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Sitemap;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Repository\PageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListPagesAction implements MiddlewareInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * ListPagesAction constructor.
     * @param PageRepository $pageRepository
     */
    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id');

        if (empty($id)) {
            return new ApiErrorResponse('invalid id');
        }

        $pages = $this->pageRepository->findBy(['sitemapId' => $id]);

        $result = [];
        foreach ($pages as $page) {
            /** @var Page $page */
            $result[] = $page->toArray();
        }

        return new ApiSuccessResponse($result);
    }
}
