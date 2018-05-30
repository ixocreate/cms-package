<?php
namespace KiwiSuite\Cms\Handler\PageVersion;

use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Message\CreatePage;
use KiwiSuite\Cms\Message\PageVersion\CreatePageVersion;
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
     * CreateSitemapHandler constructor.
     * @param PageVersionRepository $pageVersionRepository
     */
    public function __construct(PageVersionRepository $pageVersionRepository)
    {
        $this->pageVersionRepository = $pageVersionRepository;
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

        $pageVersion = new PageVersion([
            'id' => Uuid::uuid4()->toString(),
            'pageId' => $message->pageId(),
            'content' => $message->content(),
            'createdBy' => $message->createdBy(),
            'approvedAt' => $message->createdAt(),
            'createdAt' => $message->createdAt(),

        ]);
        $this->pageVersionRepository->save($pageVersion);

        return $message;
    }
}
