<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Command\Page;

use Ixocreate\Cms\Package\Entity\Page;
use Ixocreate\Cms\Package\Entity\Sitemap;
use Ixocreate\Cms\Package\PageType\PageTypeInterface;
use Ixocreate\Cms\Package\PageType\PageTypeSubManager;
use Ixocreate\Cms\Package\Repository\PageRepository;
use Ixocreate\Cms\Package\Repository\SitemapRepository;
use Ixocreate\CommandBus\Package\Command\AbstractCommand;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\CommandBus\Package\CommandInterface;
use Ixocreate\Filter\Package\FilterableInterface;
use Ixocreate\Validation\Package\ValidatableInterface;
use Ixocreate\Validation\Package\ViolationCollectorInterface;
use Ixocreate\Intl\Package\LocaleManager;
use Doctrine\DBAL\Driver\Connection;
use Ixocreate\Cache\CacheInterface;

final class AddCommand extends AbstractCommand implements CommandInterface, ValidatableInterface, FilterableInterface
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
     * @return bool
     */
    public function execute(): bool
    {
        $this->master->transactional(function () {
            /** @var Sitemap $sitemap */
            $sitemap = $this->sitemapRepository->find($this->dataValue('sitemapId'));
            /** @var PageTypeInterface $pageType */
            $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

            $page = new Page([
                'id' => $this->uuid(),
                'sitemapId' => $this->dataValue('sitemapId'),
                'locale' => $this->dataValue('locale'),
                'name' => $this->dataValue('name'),
                'status' => 'offline',
                'updatedAt' => new \DateTime(),
                'createdAt' => new \DateTime(),
                'releasedAt' => new \DateTime(),
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

    public function filter(): FilterableInterface
    {
        $newData = [];
        $newData['sitemapId'] = (string) $this->dataValue('sitemapId');
        $newData['locale'] = (string)$this->dataValue('locale');
        $newData['name'] = (string)$this->dataValue('name');
        $newData['createdBy'] = (string) $this->dataValue('createdBy');
        $newData['content'] = $this->dataValue('content');
        $newData['status'] = $this->dataValue('status');

        return $this->withData($newData);
    }

    public static function serviceName(): string
    {
        return 'cms.page-add';
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
//        if (!$this->pageTypeSubManager->has($this->dataValue('sitemapId'))) {
//            $violationCollector->add("sitemapId", "invalid_sitemapId");
//        }

//        if (empty($this->dataValue('name')) || !\is_string($this->dataValue('name'))) {
//            $violationCollector->add("name", "invalid_name");
//        }
//
//        if (!empty($this->dataValue("parentSitemapId"))) {
//            if (!\is_string($this->dataValue("parentSitemapId"))) {
//                $violationCollector->add("parentSitemapId", "invalid_parentSitemapId");
//            } else {
//                $sitemap = $this->sitemapRepository->find($this->dataValue("parentSitemapId"));
//                if (empty($sitemap)) {
//                    $violationCollector->add("parentSitemapId", "invalid_parentSitemapId");
//                }
//            }
//        }
//
//        if (!$this->localeManager->has($this->dataValue("locale"))) {
//            $violationCollector->add("locale", "invalid_locale");
//        }
    }
}
