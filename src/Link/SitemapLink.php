<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Link;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Schema\Link\LinkInterface;

final class SitemapLink implements LinkInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var Page
     */
    private $page = null;

    /**
     * @var CmsRouter
     */
    private $cmsRouter;

    /**
     * MediaLink constructor.
     * @param PageRepository $pageRepository
     * @param CmsRouter $cmsRouter
     */
    public function __construct(PageRepository $pageRepository, CmsRouter $cmsRouter)
    {
        $this->pageRepository = $pageRepository;
        $this->cmsRouter = $cmsRouter;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return \serialize($this->page);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->page = \unserialize($serialized);
    }

    /**
     * @param $value
     * @return LinkInterface
     */
    public function create($value): LinkInterface
    {
        $clone = clone $this;
        if ($value instanceof SitemapLink) {
            $clone->page = $value->page;
        } elseif (\is_string($value)) {
            $value = $this->pageRepository->find($value);
            if ($value instanceof Page) {
                $clone->page = $value;
            }
        } elseif (\is_array($value)) {
            if (!empty($value['id'])) {
                $value = $this->pageRepository->find($value['id']);
                if ($value instanceof Page) {
                    $clone->page = $value;
                }
            }
        }

        return $clone;
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return 'Sitemap';
    }

    /**
     * @return string
     */
    public function assemble(): string
    {
        if (empty($this->page)) {
            return "";
        }

        try {
            return $this->cmsRouter->fromPage($this->page);
        } catch (\Exception $exception) {
            return "";
        }
    }

    /**
     * @return mixed
     */
    public function toJson()
    {
        if (empty($this->page)) {
            return null;
        }

        return $this->page->toPublicArray();
    }

    /**
     * @return mixed
     */
    public function toDatabase()
    {
        if (empty($this->page)) {
            return null;
        }

        return (string) $this->page->id();
    }

    public static function serviceName(): string
    {
        return 'sitemap';
    }
}
