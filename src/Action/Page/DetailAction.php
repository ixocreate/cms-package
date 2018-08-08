<?php
namespace KiwiSuite\Cms\Action\Page;

use KiwiSuite\Admin\Response\ApiDetailResponse;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Cms\Resource\PageResource;
use KiwiSuite\Schema\Builder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DetailAction implements MiddlewareInterface
{
    /**
     * @var Builder
     */
    private $builder;
    /**
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var PageResource
     */
    private $pageResource;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    public function __construct(PageRepository $pageRepository, PageResource $pageResource, Builder $builder, SitemapRepository $sitemapRepository)
    {
        $this->builder = $builder;
        $this->pageRepository = $pageRepository;
        $this->pageResource = $pageResource;
        $this->sitemapRepository = $sitemapRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        /** @var Page $entity */
        $entity = $this->pageRepository->find($request->getAttribute("id"));

        $hasChildren = ($this->sitemapRepository->count(['parentId' => $entity->sitemapId()]) > 0);

        $otherLanguages = [];
        $result = $this->pageRepository->findBy(['sitemapId' => $entity->sitemapId()]);
        /** @var Page $item */
        foreach ($result as $item) {
            if ((string) $item->id() === (string) $entity->id()) {
                continue;
            }

            $otherLanguages[] = $item->toPublicArray();
        }

        $samePageTypePages = [];
        $sitemap = $this->sitemapRepository->find($entity->sitemapId());
        $result = $this->sitemapRepository->findBy(['pageType' => $sitemap->pageType()]);
        $sitemapIds = [];
        foreach ($result as $item) {
            $sitemapIds[] = (string) $item->id();
        }
        if (!empty($sitemapIds)) {
            $result = $this->pageRepository->findBy(['locale' => $entity->locale(), 'sitemapId' => $sitemapIds], ['name' => 'ASC']);

            foreach ($result as $item) {
                if ((string) $item->id() === (string) $entity->id()) {
                    continue;
                }

                $samePageTypePages[] = $item->toPublicArray();
            }
        }

        return new ApiDetailResponse(
            $this->pageResource,
            $entity->toPublicArray(),
            $this->pageResource->updateSchema($this->builder),
            [
                'hasChildren' => $hasChildren,
                'otherLanguages' => $otherLanguages,
                'samePageTypePages' => $samePageTypePages
            ]
        );
    }
}
