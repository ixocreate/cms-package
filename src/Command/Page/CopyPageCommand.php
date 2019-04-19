<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Command\Page;

use Doctrine\DBAL\Driver\Connection;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Cache\CacheInterface;
use Ixocreate\CommandBus\CommandInterface;
use Ixocreate\Filter\FilterableInterface;
use Ixocreate\Validation\ValidatableInterface;
use Ixocreate\Validation\ViolationCollectorInterface;
use Ixocreate\Intl\LocaleManager;

final class CopyPageCommand extends AbstractCommand implements CommandInterface, ValidatableInterface, FilterableInterface
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
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Page
     */
    private $toPage;

    /**
     * @var Sitemap
     */
    private $toSitemap;

    /**
     * CreateCommand constructor.
     * @param PageTypeSubManager $pageTypeSubManager
     * @param SitemapRepository $sitemapRepository
     * @param PageRepository $pageRepository
     * @param LocaleManager $localeManager
     * @param CommandBus $commandBus
     * @param Connection $master
     * @param PageVersionRepository $pageVersionRepository
     * @param CacheInterface $cms
     */
    public function __construct(
        PageTypeSubManager $pageTypeSubManager,
        SitemapRepository $sitemapRepository,
        PageRepository $pageRepository,
        LocaleManager $localeManager,
        CommandBus $commandBus,
        Connection $master,
        PageVersionRepository $pageVersionRepository,
        CacheInterface $cms
    ) {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
        $this->localeManager = $localeManager;
        $this->pageRepository = $pageRepository;
        $this->commandBus = $commandBus;
        $this->master = $master;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->cache = $cms;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        $this->master->transactional(function () {
            if (!empty($this->dataValue('fromSitemapId'))) {
                /** @var Sitemap $fromSitemap */
                $fromSitemap = $this->sitemapRepository->find($this->dataValue('fromSitemapId'));

                /** @var Page $fromPage */
                $fromPage = $this->pageRepository->findOneBy([
                    'sitemapId' => (string) $fromSitemap,
                    'locale' => $this->dataValue('fromLocale'),
                ]);
            } elseif (!empty($this->dataValue('fromPageId'))) {
                /** @var Page $fromPage */
                $fromPage = $this->pageRepository->find($this->dataValue('fromPageId'));

                /** @var Sitemap $fromSitemap */
                $fromSitemap = $this->sitemapRepository->find($fromPage->sitemapId());
            }

            /** @var PageTypeInterface $pageTypeName */
            $pageTypeName = $fromSitemap->pageType();

            $pageType = $this->pageTypeSubManager->get($pageTypeName);

            /** @var Page $toPage */
            $toPage = null;

            if (!empty($this->dataValue('toSitemapId'))) {
                $this->toSitemap = $this->sitemapRepository->find($this->dataValue('toSitemapId'));

                $this->toPage = new Page([
                    'id' => $this->uuid(),
                    'sitemapId' => $this->toSitemap->id(),
                    'locale' => $this->dataValue('locale'),
                    'name' => (!empty($this->dataValue('name'))) ? $this->dataValue('name') : $fromPage->name(),
                    'status' => 'offline',
                    'createdAt' => $this->createdAt(),
                    'updatedAt' => $this->createdAt(),
                    'releasedAt' => $this->createdAt(),
                ]);

                $this->pageRepository->save($this->toPage);

                $this->commandBus->command(SlugCommand::class, [
                    'name' => (string) $this->toPage->name(),
                    'pageId' => (string) $this->toPage->id(),
                ]);
            } elseif (!empty($this->dataValue('toPageId'))) {
                $this->toPage = $this->pageRepository->find($this->dataValue('toPageId'));

                $this->toSitemap = $this->sitemapRepository->find($fromPage->sitemapId());
            }
            $this->cache->clear();

            $query = $this->pageVersionRepository->createQuery('SELECT v FROM ' . PageVersion::class . ' v WHERE v.pageId = :pageId ORDER BY v.createdAt DESC');
            $query->setParameter('pageId', (string) $fromPage->id());
            $query->setMaxResults(1);

            $pageVersion = $query->getResult()[0];

            $this->commandBus->command(CreateVersionCommand::class, [
                'pageType' => $pageType::serviceName(),
                'pageId' => (string) $this->toPage->id(),
                'content' => $pageVersion->content(),
                'approve' => true,
                'createdBy' => $this->dataValue('createdBy'),
            ]);
        });

        return true;
    }

    public function toPage(): ?Page
    {
        return $this->toPage;
    }

    public function toSitemap(): ?Sitemap
    {
        return $this->toSitemap;
    }

    public static function serviceName(): string
    {
        return 'cms.copy-page';
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
        if (!empty($this->dataValue('fromPageId')) && \is_string($this->dataValue('fromPageId'))) {
            /** @var Page $fromPage */
            $fromPage = $this->pageRepository->find($this->dataValue('fromPageId'));
            if ($fromPage === null) {
                $violationCollector->add('fromPageId', 'invalid_fromPageId');
            }
        }

        if (!empty($this->dataValue('fromSitemapId')) && \is_string($this->dataValue('fromSitemapId'))) {
            /** @var Sitemap $fromSitemap */
            $fromSitemap = $this->sitemapRepository->find($this->dataValue('fromSitemapId'));
            if ($fromSitemap === null) {
                $violationCollector->add('fromSitemapId', 'invalid_fromSitemapId');
            }
        }

        if (!empty($this->dataValue('fromSitemapId')) && empty($this->dataValue('fromLocale'))) {
            $violationCollector->add('fromLocale', 'required_fromLocale');
        }

        if (empty($this->dataValue('fromSitemapId')) && empty($this->dataValue('fromPageId'))) {
            $violationCollector->add('fromPageId', 'invalid_parameters', 'either fromSitemapId or fromPageId are required');
        }

        if (!empty($this->dataValue('fromSitemapId')) && !empty($this->dataValue('fromPageId'))) {
            $violationCollector->add('fromPageId', 'invalid_parameters', 'only fromSitemapId or fromPageId are allowed');
        }

        if (empty($this->dataValue('createdBy')) || !\is_string($this->dataValue('createdBy'))) {
            $violationCollector->add('createdBy', 'invalid_createdBy');
        }
    }

    public function filter(): FilterableInterface
    {
        $newData = [];
        $newData['fromSitemapId'] = (string) $this->dataValue('fromSitemapId', '');
        $newData['fromLocale'] = (string) $this->dataValue('fromLocale', '');
        $newData['fromPageId'] = (string) $this->dataValue('fromPageId', '');

        $newData['toSitemapId'] = (string) $this->dataValue('toSitemapId', '');
        $newData['toLocale'] = (string) $this->dataValue('toLocale', '');
        $newData['toPageId'] = (string) $this->dataValue('toPageId', '');

        $newData['name'] = (string) $this->dataValue('name', '');
        $newData['createdBy'] = (string) $this->dataValue('createdBy', '');

        return $this->withData($newData);
    }
}
