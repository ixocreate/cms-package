<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Loader;

use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Entity\EntityCollection;

final class DatabaseSitemapLoader implements SitemapLoaderInterface
{
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var EntityCollection
     */
    private $collection;

    /**
     * @var
     */
    private $handles;

    /**
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(SitemapRepository $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     *
     */
    private function init(): void
    {
        if ($this->collection instanceof EntityCollection) {
            return;
        }

        $result = $this->sitemapRepository->findAll();
        $this->collection = new EntityCollection($result, 'id');
    }

    /**
     * @param string $sitemapId
     * @return Sitemap|null
     */
    public function receiveSitemap(string $sitemapId): ?Sitemap
    {
        $this->init();

        if (!$this->collection->has($sitemapId)) {
            return null;
        }

        return $this->collection->get($sitemapId);
    }

    public function receiveHandles(): array
    {
        if (!\is_array($this->handles)) {
            $this->handles = [];

            $this->init();

            $this->handles = $this->collection->filter(function (Sitemap $sitemap) {
                return !empty($sitemap->handle());
            })->extract('handle')->toArray();
        }

        return $this->handles;
    }
}
