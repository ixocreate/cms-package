<?php
namespace KiwiSuite\Cms\Handler;

use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Message\CreatePage;
use KiwiSuite\Cms\Message\CreateSlug;
use KiwiSuite\Cms\Message\PageVersion\CreatePageVersion;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\CommandBus;
use KiwiSuite\CommandBus\Handler\HandlerInterface;
use KiwiSuite\CommandBus\Message\MessageInterface;
use Ramsey\Uuid\Uuid;

final class AddPageHandler implements HandlerInterface
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
     * @var CreatePageVersion
     */
    private $createPageVersion;
    /**
     * @var CommandBus
     */
    private $commandBus;
    /**
     * @var CreateSlug
     */
    private $createSlug;
    /**
     * CreateSitemapHandler constructor.
     * @param PageRepository $pageRepository
     * @param PageVersionRepository $pageVersionRepository
     * @param CommandBus $commandBus
     * @param CreatePageVersion $createPageVersion
     * @param CreateSlug $createSlug
     */
    public function __construct(
        PageRepository $pageRepository,
        PageVersionRepository $pageVersionRepository,
        CommandBus $commandBus,
        CreatePageVersion $createPageVersion,
        CreateSlug $createSlug
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->createPageVersion = $createPageVersion;
        $this->commandBus = $commandBus;
        $this->createSlug = $createSlug;
    }

    /**
     * @param MessageInterface $message
     * @return MessageInterface
     */
    public function __invoke(MessageInterface $message): MessageInterface
    {
        $page = new Page([
            'id' => $message->uuid(),
            'sitemapId' => $message->sitemapId(),
            'locale' => $message->locale(),
            'name' => $message->name(),
            'status' => 'offline',
            'updatedAt' => $message->createdAt(),
            'createdAt' => $message->createdAt(),
        ]);

        /** @var Page $page */
        $page = $this->pageRepository->save($page);

        $this->saveSlug((string) $page->id());
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

    private function saveSlug(string $pageId): void
    {
        $body = [
            'pageId' => $pageId,
        ];

        $message = $this->createSlug->inject($body, []);
        $message->validate();
        $this->commandBus->handle($message);
    }
}
