<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Command\Page;

use Ixocreate\Cache\CacheInterface;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\StructureCacheable;
use Ixocreate\Cms\Config\Config;
use Ixocreate\Cms\Entity\Navigation;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Event\PageEvent;
use Ixocreate\Cms\Repository\NavigationRepository;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Event\EventDispatcher;
use Ixocreate\Filter\FilterableInterface;
use Ixocreate\Validation\ValidatableInterface;
use Ixocreate\Validation\Violation\ViolationCollectorInterface;
use Ramsey\Uuid\Uuid;

final class UpdateCommand extends AbstractCommand implements ValidatableInterface, FilterableInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var NavigationRepository
     */
    private $navigationRepository;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var PageCacheable
     */
    private $pageCacheable;

    /**
     * @var StructureCacheable
     */
    private $structureCacheable;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * UpdateCommand constructor.
     * @param PageRepository $pageRepository
     * @param CommandBus $commandBus
     * @param Config $config
     * @param NavigationRepository $navigationRepository
     * @param CacheInterface $cms
     * @param CacheManager $cacheManager
     * @param PageCacheable $pageCacheable
     * @param StructureCacheable $structureCacheable
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        PageRepository $pageRepository,
        CommandBus $commandBus,
        Config $config,
        NavigationRepository $navigationRepository,
        CacheInterface $cms,
        CacheManager $cacheManager,
        PageCacheable $pageCacheable,
        StructureCacheable $structureCacheable,
        EventDispatcher $eventDispatcher
    ) {
        $this->pageRepository = $pageRepository;
        $this->commandBus = $commandBus;
        $this->config = $config;
        $this->navigationRepository = $navigationRepository;
        $this->cache = $cms;
        $this->cacheManager = $cacheManager;
        $this->pageCacheable = $pageCacheable;
        $this->structureCacheable = $structureCacheable;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @return bool
     */
    public function execute(): bool
    {
        /** @var Page $page */
        $page = $this->pageRepository->find($this->dataValue('pageId'));

        $updated = false;
        $clearCache = false;
        if ($this->dataValue('name') !== false) {
            $updated = true;
            $page = $page->with('name', $this->dataValue('name'));
        }

        if ($this->dataValue('publishedFrom') !== false) {
            $updated = true;
            $page = $page->with('publishedFrom', $this->dataValue('publishedFrom'));

            if (empty($this->dataValue('publishedFrom'))) {
                $page = $page->with('releasedAt', $page->createdAt());
            } else {
                $page = $page->with('releasedAt', $this->dataValue('publishedFrom'));
            }
        }

        if ($this->dataValue('publishedUntil') !== false) {
            $updated = true;
            $page = $page->with('publishedUntil', $this->dataValue('publishedUntil'));
        }

        if ($this->dataValue('status') !== false) {
            $updated = true;
            $page = $page->with('status', $this->dataValue('status'));
        }

        if ($this->dataValue('slug') !== false) {
            $updated = true;
            $clearCache = true;
            $this->commandBus->command(SlugCommand::class, [
                'name' => (string)$this->dataValue('slug'),
                'pageId' => (string)$page->id(),
                'isChange' => true,
            ]);
        }

        if ($this->dataValue('navigation') !== null) {
            $updated = true;
            $queryBuilder = $this->navigationRepository->createQueryBuilder();
            $queryBuilder->delete(Navigation::class, 'nav')
                ->where('nav.pageId = :pageId')
                ->setParameter('pageId', (string)$page->id());
            $queryBuilder->getQuery()->execute();

            foreach ($this->dataValue('navigation') as $nav) {
                $navigationEntity = new Navigation([
                    'id' => Uuid::uuid4()->toString(),
                    'pageId' => (string)$page->id(),
                    'navigation' => $nav,
                ]);

                $this->navigationRepository->save($navigationEntity);
            }

            $this->cacheManager->fetch($this->structureCacheable, true);
        }

        if ($updated === true) {
            $page = $page->with('updatedAt', new \DateTime());
            $this->pageRepository->save($page);

//            if ($clearCache) {
//                $this->cache->clear();
//            } else {
            $this->cacheManager->fetch($this->pageCacheable->withPageId((string)$page->id()), true);
//            }

            $this->eventDispatcher->dispatch(PageEvent::PAGE_UPDATE, new PageEvent($page));
        }

        return true;
    }

    public static function serviceName(): string
    {
        return 'cms.page-update';
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
        if (empty($this->dataValue('pageId'))) {
            $violationCollector->add('page', 'invalid_pageId');
        } else {
            $page = $this->pageRepository->find($this->dataValue('pageId'));
            if (empty($page)) {
                $violationCollector->add('page', 'invalid_pageId');
            }
        }

        if (!empty($this->dataValue('status'))) {
            if (!\in_array($this->dataValue('status'), ['online', 'offline'])) {
                $violationCollector->add('status', 'invalid_status');
            }
        }
    }

    public function filter(): FilterableInterface
    {
        $newData = [];
        $newData['pageId'] = $this->dataValue('pageId');
        $newData['name'] = $this->dataValue('name', false);
        $newData['publishedFrom'] = $this->dataValue('publishedFrom', false);
        $newData['publishedUntil'] = $this->dataValue('publishedUntil', false);
        $newData['status'] = $this->dataValue('status', false);
        $newData['slug'] = $this->dataValue('slug', false);
        $newData['navigation'] = null;

        if (\is_array($this->dataValue('navigation'))) {
            $newData['navigation'] = [];
            $navItems = \array_map(function ($nav) {
                return $nav['name'];
            }, $this->config->navigation());

            foreach ($this->dataValue('navigation') as $nav) {
                if (\in_array($nav, $navItems)) {
                    $newData['navigation'][] = $nav;
                }
            }
        }

        return $this->withData($newData);
    }
}
