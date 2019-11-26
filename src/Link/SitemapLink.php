<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Link;

use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Schema\Link\LinkInterface;
use Ixocreate\Schema\Link\LinkListInterface;

final class SitemapLink implements LinkInterface, LinkListInterface
{
    /**
     * @var AdminConfig
     */
    private $adminConfig;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageRoute
     */
    private $pageRoute;

    /**
     * @var Page
     */
    private $page = null;

    /**
     * MediaLink constructor.
     *
     * @param PageRepository $pageRepository
     * @param PageRoute $pageRoute
     * @param AdminConfig $adminConfig
     */
    public function __construct(
        AdminConfig $adminConfig,
        PageRepository $pageRepository,
        PageRoute $pageRoute
    ) {
        $this->adminConfig = $adminConfig;
        $this->pageRepository = $pageRepository;
        $this->pageRoute = $pageRoute;
    }

    public static function serviceName(): string
    {
        return 'sitemap';
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
    public function listUrl(): string
    {
        return \rtrim((string)$this->adminConfig->uri()->getPath(), '/') . '/api/page/list';
    }

    /**
     * @return bool
     */
    public function hasLocales(): bool
    {
        return true;
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
    public function assemble(): string
    {
        if (empty($this->page) || !($this->page instanceof Page)) {
            return "";
        }

        try {
            return $this->pageRoute->fromPage($this->page);
        } catch (\Throwable $exception) {
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

    /**
     * @return string
     */
    public function serialize()
    {
        return \serialize(clone $this->page);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        try {
            $page = \unserialize($serialized);
            if ($page instanceof Page) {
                $page->id();
                $this->page = $page;
            }

        } catch (\Throwable $throwable) {

        }
    }
}
