<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Site\Tree;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\PageContentCacheable;
use Ixocreate\Cms\Cacheable\PageVersionCacheable;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Site\Structure\StructureItem;
use Ixocreate\CommonTypes\Entity\SchemaType;
use Ixocreate\Contract\Cache\CacheableInterface;
use Ixocreate\Contract\ServiceManager\SubManager\SubManagerInterface;
use Ixocreate\Entity\Type\Type;
use RecursiveIterator;

class Item implements ContainerInterface
{
    /**
     * @var StructureItem
     */
    private $structureItem;

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ItemFactory
     */
    private $itemFactory;
    /**
     * @var PageCacheable
     */
    private $pageCacheable;
    /**
     * @var SitemapCacheable
     */
    private $sitemapCacheable;
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var PageVersionCacheable
     */
    private $pageVersionCacheable;

    /**
     * Item constructor.
     * @param StructureItem $structureItem
     * @param ItemFactory $itemFactory
     * @param CacheableInterface $pageCacheable
     * @param CacheableInterface $sitemapCacheable
     * @param CacheableInterface $pageVersionCacheable
     * @param CacheManager $cacheManager
     * @param SubManagerInterface $pageTypeSubManager
     */
    public function __construct(
        StructureItem $structureItem,
        ItemFactory $itemFactory,
        CacheableInterface $pageCacheable,
        CacheableInterface $sitemapCacheable,
        CacheableInterface $pageVersionCacheable,
        CacheManager $cacheManager,
        SubManagerInterface $pageTypeSubManager
    ) {
        $this->structureItem = clone $structureItem;
        $this->itemFactory = $itemFactory;
        $this->pageCacheable = $pageCacheable;
        $this->sitemapCacheable = $sitemapCacheable;
        $this->pageVersionCacheable = $pageVersionCacheable;
        $this->cacheManager = $cacheManager;
        $this->pageTypeSubManager = $pageTypeSubManager;

        $this->container = new Container($this->structureItem->children(), $this->itemFactory);
    }

    public function count()
    {
        return $this->container->count();
    }

    public function structureItem(): StructureItem
    {
        return $this->structureItem;
    }

    /**
     * @return ContainerInterface
     */
    public function below(): ContainerInterface
    {
        return new Container($this->structureItem->children(), $this->itemFactory);
    }

    /**
     * @return PageTypeInterface
     */
    public function pageType(): PageTypeInterface
    {
        return $this->pageTypeSubManager->get($this->sitemap()->pageType());
    }

    /**
     * @param string $locale
     * @return Page
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function page(string $locale): Page
    {
        if (!\array_key_exists($locale, $this->structureItem()->pages())) {
            throw new \Exception(sprintf("Page with locale '%s' does not exists", $locale));
        }

        return $this->cacheManager->fetch(
            $this->pageCacheable
                ->withPageId($this->structureItem()->pages()[$locale])
        );
    }

    public function sitemap(): Sitemap
    {
        return $this->cacheManager->fetch(
            $this->sitemapCacheable
                ->withSitemapId($this->structureItem()->sitemapId())
        );
    }

    /**
     * @param string $locale
     * @return SchemaType
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function pageContent(string $locale): SchemaType
    {
        if (!\array_key_exists($locale, $this->structureItem()->pages())) {
            throw new \Exception(sprintf("Page with locale '%s' does not exists", $locale));
        }

        $pageVersion = $this->cacheManager->fetch(
            $this->pageVersionCacheable
                ->withPageId($this->structureItem()->pages()[$locale])
        );

        if (!($pageVersion instanceof PageVersion)) {
            return Type::create([], SchemaType::serviceName());
        }

        return $pageVersion->content();
    }

    public function level(): int
    {
        return $this->structureItem()->level();
    }

    public function handle(): ?string
    {
        return $this->structureItem->handle();
    }

    public function navigation(): array
    {
        return $this->structureItem()->navigation();
    }

    /**
     * @param Sitemap|null $currentSitemap
     * @return bool
     */
    public function isActive(?Sitemap $currentSitemap = null): bool
    {
        if (empty($currentSitemap)) {
            return false;
        }

        if ((string) $this->sitemap()->id() === (string) $currentSitemap->id()) {
            return true;
        }

        if ($currentSitemap->nestedLeft() > $this->sitemap()->nestedLeft() && $currentSitemap->nestedRight() < $this->sitemap()->nestedRight()) {
            return true;
        }

        return false;
    }

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function filter(callable $callable): ContainerInterface
    {
        return $this->container->filter($callable);
    }

    /**
     * @param int $level
     * @return ContainerInterface
     */
    public function withMaxLevel(int $level): ContainerInterface
    {
        return $this->container->withMaxLevel($level);
    }

    /**
     * @param string $navigation
     * @return ContainerInterface
     */
    public function withNavigation(string $navigation): ContainerInterface
    {
        return $this->container->withNavigation($navigation);
    }

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function where(callable $callable): ContainerInterface
    {
        return $this->container->where($callable);
    }

    /**
     * @param int $level
     * @return ContainerInterface
     */
    public function withMinLevel(int $level): ContainerInterface
    {
        return $this->container->withMinLevel($level);
    }

    /**
     * @return ContainerInterface
     */
    public function flatten(): ContainerInterface
    {
        return $this->container->flatten();
    }

    /**
     * @param callable $callable
     * @return Item|null
     */
    public function find(callable $callable): ?Item
    {
        return $this->container->find($callable);
    }

    /**
     * @param string $handle
     * @return Item|null
     */
    public function findByHandle(string $handle): ?Item
    {
        return $this->container->findByHandle($handle);
    }

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function sort(callable $callable): ContainerInterface
    {
        return $this->container->sort($callable);
    }

    /**
     * @return Item
     */
    public function current()
    {
        return $this->container->current();
    }

    /**
     *
     */
    public function next()
    {
        $this->container->next();
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->container->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->container->valid();
    }

    /**
     *
     */
    public function rewind()
    {
        $this->container->rewind();
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return $this->container->hasChildren();
    }

    /**
     * @return RecursiveIterator|void
     */
    public function getChildren()
    {
        return $this->container->getChildren();
    }
}
