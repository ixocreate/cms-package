<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Event;

use Ixocreate\Package\Cms\Entity\Page;
use Ixocreate\Package\Cms\Entity\PageVersion;
use Ixocreate\Package\Cms\Entity\Sitemap;
use Ixocreate\Package\Cms\PageType\PageTypeInterface;
use Ixocreate\Package\Event\Event;

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
