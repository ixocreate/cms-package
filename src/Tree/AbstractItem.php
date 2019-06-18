<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Tree\Structure\StructureItem;

abstract class AbstractItem extends AbstractContainer implements ItemInterface
{
    /**
     * @var StructureItem
     */
    private $structureItem;

    /**
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    public function __construct(
        StructureItem $structureItem,
        FactoryInterface $factory,
        PageTypeSubManager $pageTypeSubManager,
        array $filter = []
    ) {
        $this->structureItem = $structureItem;
        $this->factory = $factory;
        $this->pageTypeSubManager = $pageTypeSubManager;

        parent::__construct($this->structureItem()->structure(), $factory, $filter);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    protected function locale(string $locale = null): string
    {
        if ($locale !== null) {
            return $locale;
        }

        return \Locale::getDefault();
    }

    /**
     * @throws \Exception
     * @return Sitemap
     */
    final public function sitemap(): Sitemap
    {
        return new Sitemap($this->structureItem->sitemapData());
    }

    /**
     * @param string $locale
     * @throws \Exception
     * @return bool
     */
    final public function hasPage(string $locale = null): bool
    {
        $locale = $this->locale($locale);
        return !empty($this->structureItem->pageData($locale));
    }

    /**
     * @param string $locale
     * @throws \Exception
     * @return Page
     */
    final public function page(string $locale): Page
    {
        $locale = $this->locale($locale);
        if (!$this->hasPage($locale)) {
            //TODO Exception
            throw new \Exception("Invalid page");
        }

        return new Page($this->structureItem->pageData($locale));
    }

    /**
     * @param string $locale
     * @throws \Exception
     * @return bool
     */
    final public function isOnline(string $locale): bool
    {
        $locale = $this->locale($locale);
        if (!$this->hasPage($locale)) {
            return false;
        }

        $page = $this->page($locale);

        if (!$page->isOnline()) {
            return false;
        }

        $parent = $this->parent();
        if (empty($parent)) {
            return true;
        }

        return $parent->isOnline($locale);
    }

    /**
     * @throws \Exception
     * @return PageTypeInterface
     */
    final public function pageType(): PageTypeInterface
    {
        return $this->pageTypeSubManager->get($this->structureItem->pageType());
    }

    /**
     * @throws \Exception
     * @return string|null
     */
    final public function handle(): ?string
    {
        return $this->structureItem->handle();
    }

    /**
     * @param string $locale
     * @throws \Exception
     * @return array
     */
    final public function navigation(string $locale): array
    {
        return $this->structureItem->navigation($locale);
    }

    /**
     * @return int
     */
    final public function level(): int
    {
        return $this->structureItem->level();
    }

    /**
     * @return ItemInterface|null
     */
    final public function parent(): ?ItemInterface
    {
        $parent = $this->structureItem->parent();
        if (empty($parent)) {
            return null;
        }

        return $this->factory->createItem($parent);
    }

    /**
     * @return StructureItem
     */
    final public function structureItem(): StructureItem
    {
        return $this->structureItem;
    }

    /**
     * @return ContainerInterface
     * @throws \Exception
     */
    final public function below(): ContainerInterface
    {
        return $this->factory->createContainer($this->structureItem->structure());
    }
}
