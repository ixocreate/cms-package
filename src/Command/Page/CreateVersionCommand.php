<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Command\Page;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Package\Cms\Cacheable\PageVersionCacheable;
use Ixocreate\Package\Cms\Entity\Page;
use Ixocreate\Package\Cms\Entity\PageVersion;
use Ixocreate\Package\Cms\Entity\Sitemap;
use Ixocreate\Package\Cms\Event\PageEvent;
use Ixocreate\Package\Cms\PageType\PageTypeInterface;
use Ixocreate\Package\Cms\PageType\PageTypeSubManager;
use Ixocreate\Package\Cms\Repository\PageRepository;
use Ixocreate\Package\Cms\Repository\PageVersionRepository;
use Ixocreate\Package\Cms\Repository\SitemapRepository;
use Ixocreate\Package\CommandBus\Command\AbstractCommand;
use Ixocreate\CommonTypes\Entity\SchemaType;
use Ixocreate\Package\Filter\FilterableInterface;
use Ixocreate\Package\Validation\ValidatableInterface;
use Ixocreate\Package\Validation\ViolationCollectorInterface;
use Ixocreate\Entity\Type\Type;
use Ixocreate\Package\Event\EventDispatcher;

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
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var PageVersionCacheable
     */
    private $pageVersionCacheable;

    /**
     * CreateVersionCommand constructor.
     * @param PageVersionRepository $pageVersionRepository
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     * @param PageTypeSubManager $pageTypeSubManager
     * @param EventDispatcher $eventDispatcher
     * @param CacheManager $cacheManager
     * @param PageVersionCacheable $pageVersionCacheable
     */
    public function __construct(
        PageVersionRepository $pageVersionRepository,
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository,
        PageTypeSubManager $pageTypeSubManager,
        EventDispatcher $eventDispatcher,
        CacheManager $cacheManager,
        PageVersionCacheable $pageVersionCacheable
    ) {
        $this->pageVersionRepository = $pageVersionRepository;
        $this->pageRepository = $pageRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->cacheManager = $cacheManager;
        $this->pageVersionCacheable = $pageVersionCacheable;
    }

    /**
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function execute(): bool
    {
        /** @var Page $page */
        $page = $this->pageRepository->find($this->dataValue("pageId"));
        /** @var PageTypeInterface $pageType */
        $pageType = $this->pageTypeSubManager->get($this->dataValue("pageType"));
        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($page->sitemapId());

        if ($this->dataValue("approve") === true) {
            $queryBuilder = $this->pageVersionRepository->createQueryBuilder();
            $queryBuilder->update(PageVersion::class, "version")
                ->set("version.approvedAt", ":approvedAt")
                ->setParameter("approvedAt", null)
                ->where("version.pageId = :pageId")
                ->setParameter("pageId", (string) $page->id());
            $queryBuilder->getQuery()->execute();
        }

        $content = $this->dataValue('content');
        if (! ($content instanceof SchemaType)) {
            $content = Type::create(
                $this->dataValue('content'),
                SchemaType::class,
                [
                    'provider' => [
                        'class' => PageTypeSubManager::class,
                        'name' => $pageType::serviceName(),
                    ],
                ]
            );
        }

        $pageVersion = new PageVersion([
            'id' => $this->uuid(),
            'pageId' => (string) $page->id(),
            'content' => $content,
            'createdBy' => $this->dataValue('createdBy'),
            'approvedAt' => ($this->dataValue('approve') === true) ? $this->createdAt() : null,
            'createdAt' => $this->createdAt(),
        ]);

        /** @var PageVersion $pageVersion */
        $pageVersion = $this->pageVersionRepository->save($pageVersion);

        if ($this->dataValue("approve") === true) {
            $this->cacheManager->fetch($this->pageVersionCacheable->withPageId((string) $page->id()), true);
        }

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
