<?php
namespace Ixocreate\Cms\Event;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Event\Event;

final class PageEvent extends Event
{
    /**
     * @var Sitemap
     */
    private $sitemap;
    /**
     * @var Page
     */
    private $page;
    /**
     * @var PageVersion
     */
    private $pageVersion;
    /**
     * @var PageTypeInterface
     */
    private $pageType;

    public function __construct(Sitemap $sitemap, Page $page, PageVersion $pageVersion, PageTypeInterface $pageType)
    {
        $this->sitemap = $sitemap;
        $this->page = $page;
        $this->pageVersion = $pageVersion;
        $this->pageType = $pageType;
    }

    /**
     * @return Sitemap
     */
    public function sitemap(): Sitemap
    {
        return $this->sitemap;
    }

    /**
     * @return Page
     */
    public function page(): Page
    {
        return $this->page;
    }

    /**
     * @return PageVersion
     */
    public function pageVersion(): PageVersion
    {
        return $this->pageVersion;
    }

    /**
     * @return PageTypeInterface
     */
    public function pageType(): PageTypeInterface
    {
        return $this->pageType;
    }
}
