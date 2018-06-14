<?php
namespace KiwiSuite\Cms\Handler\PageVersion;

use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Message\CreatePage;
use KiwiSuite\Cms\Message\PageVersion\CreatePageVersion;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\Handler\HandlerInterface;
use KiwiSuite\CommandBus\Message\MessageInterface;
use Ramsey\Uuid\Uuid;

final class CreatePageVersionHandler implements HandlerInterface
{
    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;
    /**
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * CreateSitemapHandler constructor.
     * @param PageVersionRepository $pageVersionRepository
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(
        PageVersionRepository $pageVersionRepository,
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository
    ) {
        $this->pageVersionRepository = $pageVersionRepository;
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
    }

    public function __invoke(MessageInterface $message): MessageInterface
    {
        /** @var CreatePageVersion $message */

        $queryBuilder = $this->pageVersionRepository->createQueryBuilder();
        $queryBuilder->update(PageVersion::class, "version")
            ->set("version.approvedAt", ":approvedAt")
            ->setParameter("approvedAt", null)
            ->where("version.pageId = :pageId")
            ->setParameter("pageId", $message->pageId());

        $queryBuilder->getQuery()->execute();

        /** @var Page $page */
        $page = $this->pageRepository->find($message->pageId());
        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($page->sitemapId());


        $pageVersion = new PageVersion([
            'id' => Uuid::uuid4()->toString(),
            'pageId' => $message->pageId(),
            'content' => [
                '__receiver__' => [
                    'receiver' => PageTypeSubManager::class,
                    'options' => [
                        'pageType' => $sitemap->pageType()
                    ]
                ],
                '__value__' => $message->content(),
            ],
            'createdBy' => $message->createdBy(),
            'approvedAt' => $message->createdAt(),
            'createdAt' => $message->createdAt(),

        ]);
        $this->pageVersionRepository->save($pageVersion);

        return $message;
    }
}
