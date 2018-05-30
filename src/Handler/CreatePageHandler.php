<?php
namespace KiwiSuite\Cms\Handler;

use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Message\CreatePage;
use KiwiSuite\Cms\Message\PageVersion\CreatePageVersion;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\CommandBus;
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
     * @var CreatePageVersion
     */
    private $createPageVersion;
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * CreateSitemapHandler constructor.
     * @param SitemapRepository $sitemapRepository
     * @param PageRepository $pageRepository
     * @param PageVersionRepository $pageVersionRepository
     * @param CommandBus $commandBus
     * @param CreatePageVersion $createPageVersion
     */
    public function __construct(
        SitemapRepository $sitemapRepository,
        PageRepository $pageRepository,
        PageVersionRepository $pageVersionRepository,
        CommandBus $commandBus,
        CreatePageVersion $createPageVersion
    ) {
        $this->sitemapRepository = $sitemapRepository;
        $this->pageRepository = $pageRepository;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->createPageVersion = $createPageVersion;
        $this->commandBus = $commandBus;
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
            'id' => $message->uuid(),
            'sitemapId' => $sitemap->id(),
            'locale' => $message->locale(),
            'name' => $message->name(),
            'status' => 'offline',
            'updatedAt' => $message->createdAt(),
            'createdAt' => $message->createdAt(),
        ]);

        /** @var Page $page */
        $page = $this->pageRepository->save($page);

        $this->savePageVersion((string) $page->id(), (string) $message->createdBy());

        return $message;
    }

    private function savePageVersion(string $pageId, string $createdAt): void
    {
        $body = [
            'content' => [],
        ];

        $metadata = [
            User::class => $createdAt,
            'id' => $pageId,
        ];

        $message = $this->createPageVersion->inject($body, $metadata);
        $message->validate();
        $this->commandBus->handle($message);
    }
}
