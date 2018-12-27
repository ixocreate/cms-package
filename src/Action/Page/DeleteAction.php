<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteAction implements MiddlewareInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    public function __construct(PageRepository $pageRepository, PageVersionRepository $pageVersionRepository, SitemapRepository $sitemapRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->sitemapRepository = $sitemapRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute("id");

        /** @var Page $page */
        $page = $this->pageRepository->find($id);
        if (empty($page)) {
            return new ApiErrorResponse("invalid_request");
        }

        $pageVersions = $this->pageVersionRepository->findBy(['pageId' => $page->id()]);

        foreach ($pageVersions as $pageVersion) {
            $this->pageVersionRepository->remove($pageVersion);
        }

        $this->pageRepository->remove($page);

        if ($this->pageRepository->count(['sitemapId' => $page->sitemapId()]) === 0) {
            $sitemap = $this->sitemapRepository->find($page->sitemapId());
            $this->sitemapRepository->remove($sitemap);
        }

        return new ApiSuccessResponse();
    }
}
