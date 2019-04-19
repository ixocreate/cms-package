<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Command\Page;

use Doctrine\DBAL\Driver\Connection;
use Ixocreate\Cache\CacheInterface;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\HandlePageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\CommandBus\CommandInterface;
use Ixocreate\Filter\FilterableInterface;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\Validation\ValidatableInterface;
use Ixocreate\Validation\ViolationCollectorInterface;

final class CreateCommand extends AbstractCommand implements CommandInterface, ValidatableInterface, FilterableInterface
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var Connection
     */
    private $master;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * CreateCommand constructor.
     * @param PageTypeSubManager $pageTypeSubManager
     * @param SitemapRepository $sitemapRepository
     * @param PageRepository $pageRepository
     * @param LocaleManager $localeManager
     * @param CommandBus $commandBus
     * @param Connection $master
     * @param CacheInterface $cms
     */
    public function __construct(
        PageTypeSubManager $pageTypeSubManager,
        SitemapRepository $sitemapRepository,
        PageRepository $pageRepository,
        LocaleManager $localeManager,
        CommandBus $commandBus,
        Connection $master,
        CacheInterface $cms
    ) {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
        $this->localeManager = $localeManager;
        $this->pageRepository = $pageRepository;
        $this->commandBus = $commandBus;
        $this->master = $master;
        $this->cache = $cms;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        $this->master->transactional(function () {
            /** @var PageTypeInterface $pageType */
            $pageType = $this->pageTypeSubManager->get($this->dataValue("pageType"));

            $sitemap = new Sitemap([
                'id' => $this->uuid(),
                'pageType' => $pageType::serviceName(),
            ]);

            if (\is_subclass_of($pageType, HandlePageTypeInterface::class)) {
                $sitemap = $sitemap->with("handle", $pageType::serviceName());
            }

            if (empty($this->dataValue('parentSitemapId'))) {
                $sitemap = $this->sitemapRepository->createRoot($sitemap);
            } else {
                /** @var Sitemap $parent */
                $parent = $this->sitemapRepository->find($this->dataValue('parentSitemapId'));
                $sitemap = $this->sitemapRepository->insertAsLastChild($sitemap, $parent);
            }

            $page = new Page([
                'id' => ($this->dataValue('pageId')) ?? $this->uuid(),
                'sitemapId' => $sitemap->id(),
                'locale' => $this->dataValue('locale'),
                'name' => $this->dataValue('name'),
                'status' => 'offline',
                'updatedAt' => $this->createdAt(),
                'createdAt' => $this->createdAt(),
                'releasedAt' => $this->createdAt(),
            ]);

            /** @var Page $page */
            $page = $this->pageRepository->save($page);

            $this->cache->clear();

            $this->commandBus->command(SlugCommand::class, [
                'name' => (string) $page->name(),
                'pageId' => (string) $page->id(),
            ]);
        });

        return true;
    }

    public static function serviceName(): string
    {
        return 'cms.page-create';
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
        if (!$this->pageTypeSubManager->has($this->dataValue('pageType'))) {
            $violationCollector->add("pageType", "invalid_pageType");
        }

        if (empty($this->dataValue('name')) || !\is_string($this->dataValue('name'))) {
            $violationCollector->add("name", "invalid_name");
        }

        if (!empty($this->dataValue("parentSitemapId"))) {
            if (!\is_string($this->dataValue("parentSitemapId"))) {
                $violationCollector->add("parentSitemapId", "invalid_parentSitemapId");
            } else {
                $sitemap = $this->sitemapRepository->find($this->dataValue("parentSitemapId"));
                if (empty($sitemap)) {
                    $violationCollector->add("parentSitemapId", "invalid_parentSitemapId");
                }
            }
        }

        if (!$this->localeManager->has((string) $this->dataValue("locale"))) {
            $violationCollector->add("locale", "invalid_locale");
        }
    }

    public function filter(): FilterableInterface
    {
        $newData = [];
        $newData['pageType'] = (string) $this->dataValue('pageType');
        $newData['parentSitemapId'] = $this->dataValue('parentSitemapId');
        $newData['locale'] = (string) $this->dataValue('locale');
        $newData['name'] = (string) $this->dataValue('name');
        $newData['createdBy'] = (string) $this->dataValue('createdBy');
        $newData['content'] = $this->dataValue('content');
        $newData['status'] = $this->dataValue('status');
        if (!empty($this->dataValue('pageId'))) {
            $newData['pageId'] = (string) $this->dataValue('pageId');
        }

        return $this->withData($newData);
    }
}
