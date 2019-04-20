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
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\CommandBus\CommandInterface;
use Ixocreate\Filter\FilterableInterface;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\Validation\ValidatableInterface;
use Ixocreate\Validation\ViolationCollectorInterface;
use Ramsey\Uuid\Uuid;

final class CopySitemapCommand extends AbstractCommand implements CommandInterface, ValidatableInterface, FilterableInterface
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

            /** @var Sitemap $fromSitemap */
            $fromSitemap = $this->sitemapRepository->find($this->dataValue('fromSitemapId'));

            /** @var PageTypeInterface $pageTypeName */
            $pageTypeName = $fromSitemap->pageType();

            $pageType = $this->pageTypeSubManager->get($pageTypeName);
            $sitemap = new Sitemap([
                'id' => $this->uuid(),
                'pageType' => $pageTypeName,
            ]);

            if (!empty($this->dataValue('parentId'))) {
                /** @var Sitemap $parent */
                $parent = $this->sitemapRepository->find($this->dataValue('parentId'));
                $sitemap = $this->sitemapRepository->insertAsFirstChild($sitemap, $parent);
            } elseif (!empty($this->dataValue('prevSiblingId'))) {
                /** @var Sitemap $sibling */
                $sibling = $this->sitemapRepository->find($this->dataValue('prevSiblingId'));
                $sitemap = $this->sitemapRepository->insertAsNextSibling($sitemap, $sibling);
            } else {
                $sitemap = $this->sitemapRepository->createRoot($sitemap);
            }

            $criteria = ['sitemapId' => (string) $fromSitemap->id()];
            if (!empty($this->dataValue('locales'))) {
                $criteria['locale'] = $this->dataValue('locales');
            }

            $pages = $this->pageRepository->findBy($criteria);

            foreach ($pages as $referencePage) {

                /** @var Page $referencePage */
                $query = $this->pageVersionRepository->createQuery('SELECT v FROM ' . PageVersion::class . ' v WHERE v.pageId = :pageId ORDER BY v.createdAt DESC');
                $query->setParameter('pageId', (string) $referencePage->id());
                $query->setMaxResults(1);

                foreach ($query->getResult() as $pageVersion) {
                    /** @var PageVersion $pageVersion */
                    $page = new Page([
                        'id' => Uuid::uuid4(),
                        'sitemapId' => $sitemap->id(),
                        'locale' => $referencePage->locale(),
                        'name' => $referencePage->name(),
                        'status' => 'offline',
                        'createdAt' => $this->createdAt(),
                        'updatedAt' => $this->createdAt(),
                        'releasedAt' => $this->createdAt(),
                    ]);

                    /** @var Page $page */
                    $this->pageRepository->save($page);

                    $this->commandBus->command(SlugCommand::class, [
                        'name' => (string) $page->name(),
                        'pageId' => (string) $page->id(),
                    ]);

                    $this->cache->clear();

                    $this->commandBus->command(CreateVersionCommand::class, [
                        'pageType' => $pageType::serviceName(),
                        'pageId' => (string) $page->id(),
                        'content' => $pageVersion->content(),
                        'approve' => true,
                        'createdBy' => $this->dataValue('createdBy'),
                    ]);
                }
            }
        });

        return true;
    }

    public static function serviceName(): string
    {
        return 'cms.copy-sitemap';
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
        if (empty($this->dataValue('fromSitemapId')) || !\is_string($this->dataValue('fromSitemapId'))) {
            $violationCollector->add('fromSitemapId', 'invalid_fromSitemapId');
        }

        if (!empty($this->dataValue('parentId'))) {
            if (!\is_string($this->dataValue('parentId'))) {
                $violationCollector->add('parentId', 'invalid_parentId');
            } else {
                $sitemap = $this->sitemapRepository->find($this->dataValue('parentId'));
                if (empty($sitemap)) {
                    $violationCollector->add('parentId', 'invalid_parentId');
                }
            }
        }

        if (!empty($this->dataValue('prevSiblingId'))) {
            if (!\is_string($this->dataValue('prevSiblingId'))) {
                $violationCollector->add('prevSiblingId', 'invalid_prevSiblingId');
            } else {
                $sitemap = $this->sitemapRepository->find($this->dataValue('prevSiblingId'));
                if (empty($sitemap)) {
                    $violationCollector->add('prevSiblingId', 'invalid_prevSiblingId');
                }
            }
        }
        if (empty($this->dataValue('parentId')) && empty($this->dataValue('prevSiblingId'))) {
            $violationCollector->add('invalid_data', 'invalid_data');
        }

        if (!empty($this->dataValue('locales'))) {
            $criteria = [
                'sitemapId' => $this->dataValue('fromSitemapId'),
                //'locale' => $this->dataValue('locales'),
            ];

            if ($this->pageRepository->count($criteria) === 0) {
                $violationCollector->add('locales', 'no_pages_to_copy');
            }
        }

        if (empty($this->dataValue('createdBy')) || !\is_string($this->dataValue('createdBy'))) {
            $violationCollector->add('createdBy', 'invalid_createdBy');
        }
    }

    public function filter(): FilterableInterface
    {
        $newData = [];
        $newData['fromSitemapId'] = (string) $this->dataValue('fromSitemapId');
        $newData['parentId'] = $this->dataValue('parentId');
        $newData['prevSiblingId'] = $this->dataValue('prevSiblingId');
        $newData['createdBy'] = (string) $this->dataValue('createdBy');

        $locales = $this->dataValue('locales');
        if (!empty($locales) && \is_array($locales)) {
            $newData['locales'] = $locales;
        }

        return $this->withData($newData);
    }
}
