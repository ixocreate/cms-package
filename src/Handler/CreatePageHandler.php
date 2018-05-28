<?php
namespace KiwiSuite\Cms\Handler;

use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Message\CreatePage;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\Handler\HandlerInterface;
use KiwiSuite\CommandBus\Message\MessageInterface;
use Ramsey\Uuid\Uuid;

final class CreatePageHandler implements HandlerInterface
{

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * CreateSitemapHandler constructor.
     * @param SitemapRepository $sitemapRepository
     * @param PageRepository $pageRepository
     * @param PageVersionRepository $pageVersionRepository
     */
    public function __construct(SitemapRepository $sitemapRepository, PageRepository $pageRepository, PageVersionRepository $pageVersionRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
        $this->pageRepository = $pageRepository;
        $this->pageVersionRepository = $pageVersionRepository;
    }

    public function __invoke(MessageInterface $message): MessageInterface
    {
        /** @var CreatePage $message */
        $sitemap = new Sitemap([
            'id' => $message->uuid(),
            'pageType' => $message->pageType(),
        ]);

        if (empty($message->parentSitemapId())) {
            $sitemap = $this->sitemapRepository->createRoot($sitemap);
        } else {
            /** @var Sitemap $parent */
            $parent = $this->sitemapRepository->find($message->parentSitemapId());
            $sitemap = $this->sitemapRepository->insertAsLastChild($sitemap, $parent);
        }

        $page = new Page([
            'id' => Uuid::uuid4()->toString(),
            'sitemapId' => $sitemap->id(),
            'locale' => $message->locale(),
            'name' => $message->name(),
            'status' => 'offline',
            'updatedAt' => $message->createdAt(),
            'createdAt' => $message->createdAt(),
        ]);

        /** @var Page $page */
        $page = $this->pageRepository->save($page);

        $pageVersion = new PageVersion([
            'id' => Uuid::uuid4()->toString(),
            'pageId' => $page->id(),
            'content' => [],
            'createdBy' => $message->createdBy(),
            'approvedAt' => $message->createdAt(),
            'createdAt' => $message->createdAt(),

        ]);
        $this->pageVersionRepository->save($pageVersion);

        return $message;
    }
}
