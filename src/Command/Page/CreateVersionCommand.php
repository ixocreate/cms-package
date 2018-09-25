<?php
namespace KiwiSuite\Cms\Command\Page;

use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Event\PageEvent;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\Command\AbstractCommand;
use KiwiSuite\CommonTypes\Entity\SchemaType;
use KiwiSuite\Contract\Filter\FilterableInterface;
use KiwiSuite\Contract\Validation\ValidatableInterface;
use KiwiSuite\Contract\Validation\ViolationCollectorInterface;
use KiwiSuite\Entity\Type\Type;
use KiwiSuite\Event\EventDispatcher;

final class CreateVersionCommand extends AbstractCommand implements FilterableInterface, ValidatableInterface
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
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * CreateVersionCommand constructor.
     * @param PageVersionRepository $pageVersionRepository
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     * @param PageTypeSubManager $pageTypeSubManager
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        PageVersionRepository $pageVersionRepository,
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository,
        PageTypeSubManager $pageTypeSubManager,
        EventDispatcher $eventDispatcher
    ) {
        $this->pageVersionRepository = $pageVersionRepository;
        $this->pageRepository = $pageRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute(): bool
    {
        /** @var Page $page */
        $page = $this->pageRepository->find($this->dataValue("pageId"));
        /** @var PageTypeInterface $pageType */
        $pageType = $this->pageTypeSubManager->get($this->dataValue("pageType"));
        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($page->id());

        if ($this->dataValue("approve") === true) {
            $queryBuilder = $this->pageVersionRepository->createQueryBuilder();
            $queryBuilder->update(PageVersion::class, "version")
                ->set("version.approvedAt", ":approvedAt")
                ->setParameter("approvedAt", null)
                ->where("version.pageId = :pageId")
                ->setParameter("pageId", (string) $page->id());
            $queryBuilder->getQuery()->execute();
        }

        $content = [
            '__receiver__' => [
                'receiver' => PageTypeSubManager::class,
                'options' => [
                    'pageType' => $pageType::serviceName()
                ]
            ],
            '__value__' => $this->dataValue("content"),
        ];

        $pageVersion = new PageVersion([
            'id' => $this->uuid(),
            'pageId' => (string) $page->id(),
            'content' => Type::create($content, SchemaType::class)->convertToDatabaseValue(),
            'createdBy' => $this->dataValue("createdBy"),
            'approvedAt' => ($this->dataValue("approve") === true) ? $this->createdAt() : null,
            'createdAt' => $this->createdAt(),
        ]);

        /** @var PageVersion $pageVersion */
        $pageVersion = $this->pageVersionRepository->save($pageVersion);

        $pageEvent = new PageEvent(
            $sitemap,
            $page,
            $pageVersion,
            $pageType
        );

        $this->eventDispatcher->dispatch('page-version.publish', $pageEvent);

        return true;
    }

    public static function serviceName(): string
    {
        return 'cms.page-version-create';
    }

    public function filter(): FilterableInterface
    {
        $newData = [];
        $newData['pageType'] = (string) $this->dataValue('pageType');
        $newData['pageId'] = (string) $this->dataValue('pageId');
        $newData['createdBy'] = (string) $this->dataValue('createdBy');
        $newData['content'] = $this->dataValue('content', []);
        $newData['approve'] = (bool) $this->dataValue('approve', false);

        return $this->withData($newData);
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
        if (!$this->pageTypeSubManager->has($this->dataValue('pageType'))) {
            $violationCollector->add("pageType", "invalid_pageType");
        }

        $page = $this->pageRepository->find($this->dataValue("pageId"));
        if (empty($page)) {
            $violationCollector->add("page", "invalid_page");
        }
    }
}