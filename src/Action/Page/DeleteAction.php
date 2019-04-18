<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Action\Page;

use Ixocreate\Package\Admin\Response\ApiErrorResponse;
use Ixocreate\Package\Admin\Response\ApiSuccessResponse;
use Ixocreate\Package\Cms\Entity\Page;
use Ixocreate\Package\Cms\Repository\OldRedirectRepository;
use Ixocreate\Package\Cms\Repository\PageRepository;
use Ixocreate\Package\Cms\Repository\PageVersionRepository;
use Ixocreate\Package\Cms\Repository\SitemapRepository;
use Ixocreate\Cache\CacheInterface;
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

    /**
     * @var OldRedirectRepository
     */
    private $oldRedirectRepository;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(PageRepository $pageRepository, PageVersionRepository $pageVersionRepository, SitemapRepository $sitemapRepository, OldRedirectRepository $oldRedirectRepository, CacheInterface $cms)
    {
        $this->pageRepository = $pageRepository;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->oldRedirectRepository = $oldRedirectRepository;
        $this->cache = $cms;
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

        $pageRedirects = $this->oldRedirectRepository->findBy(['pageId' => $page->id()]);

        foreach ($pageRedirects as $pageRedirect) {
            $this->oldRedirectRepository->remove(($pageRedirect));
        }

        $this->pageRepository->remove($page);

        if ($this->pageRepository->count(['sitemapId' => $page->sitemapId()]) === 0) {
            $sitemap = $this->sitemapRepository->find($page->sitemapId());
            $this->sitemapRepository->remove($sitemap);
        }

        $this->cache->clear();

        return new ApiSuccessResponse();
    }
}
