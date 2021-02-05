<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Event;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Event\Event;

final class PageEvent extends Event
{
    const PAGE_CREATE = 'page.create';

    const PAGE_UPDATE = 'page.update';

    const PAGE_DELETE = 'page.delete';

    const PAGE_VERSION_CREATE = 'page-version.create';

    const PAGE_VERSION_PUBLISH = 'page-version.publish';

    /**
     * @var Page
     */
    private $page;

    /**
     * @var Sitemap
     */
    private $sitemap;

    /**
     * @var PageVersion|null
     */
    private $pageVersion;

    /**
     * @var PageTypeInterface|null
     */
    private $pageType;

    public function __construct(Page $page, ?Sitemap $sitemap = null, ?PageVersion $pageVersion = null, ?PageTypeInterface $pageType = null)
    {
        $this->page = $page;
        $this->sitemap = $sitemap;
        $this->pageVersion = $pageVersion;
        $this->pageType = $pageType;
    }

    /**
     * @return Sitemap|null
     */
    public function sitemap(): ?Sitemap
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
     * @return PageVersion|null
     */
    public function pageVersion(): ?PageVersion
    {
        return $this->pageVersion;
    }

    /**
     * @return PageTypeInterface|null
     */
    public function pageType(): ?PageTypeInterface
    {
        return $this->pageType;
    }
}
