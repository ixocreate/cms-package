<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Doctrine\ORM\Query;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FlatListAction implements MiddlewareInterface
{
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    public function __construct(SitemapRepository $sitemapRepository, PageTypeSubManager $pageTypeSubManager, PageRepository $pageRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->pageRepository = $pageRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parentSitemap = $this->sitemapRepository->findOneBy(['handle' => $request->getAttribute('handle')]);
        if (!$parentSitemap) {
            return new ApiErrorResponse('invalid_handle');
        }

        $locale = $request->getQueryParams()['locale'] ?? null;
        if (!$locale) {
            return new ApiErrorResponse('invalid locale');
        }

        /**
         * assemble query
         * searchable page name and release date
         */
        $parameters = ['parentId' => (string)$parentSitemap->id()];
        $dql = 'FROM ' . Sitemap::class . ' s LEFT JOIN ' . Page::class . ' p WITH (s.id = p.sitemapId) WHERE s.parentId = :parentId';
        if ($term = $request->getQueryParams()['term'] ?? null) {
            $parameters += ['search' => '%' . $term . '%'];
            $dql .= ' AND (p.name LIKE :search OR p.releasedAt LIKE :search)';
        }
        $dql .= ' ORDER BY p.releasedAt DESC';

        $count = $this->sitemapRepository->createQuery('SELECT COUNT(s) ' . $dql)
            ->execute($parameters, Query::HYDRATE_SINGLE_SCALAR);

        $result = $this->sitemapRepository->createQuery('SELECT s ' . $dql)
            ->setMaxResults(\min(25, (int)($request->getQueryParams()['limit'] ?? 25)))
            ->setFirstResult(\min((int)($request->getQueryParams()['offset'] ?? 0), $count))
            ->execute($parameters);

        /**
         * map results to select options
         */
        $items = [];
        foreach ($result as $item) {
            /** @var Sitemap $item */
            $pageResult = $this->pageRepository->findBy(['sitemapId' => (string)$item->id()]);
            foreach ($pageResult as $page) {
                /** @var Page $page */
                if ($page->locale() === $locale) {
                    $items[] = [
                        'id' => $page->id(),
                        'name' => $page->releasedAt()->format('Y-m-d') . ' ' . $page->name() . ' (' . $locale . ')',
                    ];
                }
            }
        }

        return new ApiSuccessResponse($items);
    }
}
