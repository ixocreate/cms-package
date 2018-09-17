<?php
namespace KiwiSuite\Cms\Site\Admin;

use KiwiSuite\Cms\Loader\PageLoaderInterface;
use KiwiSuite\Cms\Loader\SitemapLoaderInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Router\PageRoute;
use RecursiveIterator;

final class Container implements \RecursiveIterator, \JsonSerializable, \Countable
{
    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var array
     */
    private $children = [];

    /**
     * Container constructor.
     * @param SitemapLoaderInterface $sitemapLoader
     * @param PageLoaderInterface $pageLoader
     * @param PageTypeSubManager $pageTypeSubManager
     * @param PageRoute $pageRoute
     * @param array $children
     */
    public function __construct(
        SitemapLoaderInterface $sitemapLoader,
        PageLoaderInterface $pageLoader,
        PageTypeSubManager $pageTypeSubManager,
        PageRoute $pageRoute,
        array $children
    ) {
        foreach ($children as $child) {
            $this->children[] = new Item(
                $sitemapLoader,
                $pageLoader,
                $pageTypeSubManager,
                $pageRoute,
                $child
            );
        }
    }

    /**
     * @param callable $callable
     * @return Item|null
     */
    public function findOneBy(callable $callable): ?Item
    {
        $iterator = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);
        /** @var Item $item */
        foreach ($iterator as $item) {
            if ($callable($item) === true) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return Item
     */
    public function current()
    {
        return $this->children[$this->index];
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->children[$this->index]);
    }

    /**
     *
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return (count($this->children) > 0);
    }

    /**
     * @return RecursiveIterator
     */
    public function getChildren()
    {
        return $this->children[$this->index];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->children;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->children);
    }
}