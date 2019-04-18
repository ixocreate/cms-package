<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Cacheable;

use Ixocreate\Package\Cms\Repository\SitemapRepository;
use Ixocreate\Package\Type\Entity\UuidType;
use Ixocreate\Cache\CacheableInterface;

final class SitemapCacheable implements CacheableInterface
{
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var UuidType|string
     */
    private $sitemapId;

    /**
     * SitemapCacheable constructor.
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(SitemapRepository $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
    }

    public function withSitemapId($sitemapId): SitemapCacheable
    {
        $cacheable = clone $this;
        $cacheable->sitemapId = $sitemapId;

        return $cacheable;
    }

    /**
     * @return mixed
     */
    public function uncachedResult()
    {
        return $this->sitemapRepository->find($this->sitemapId);
    }

    /**
     * @return string
     */
    public function cacheName(): string
    {
        return 'cms';
    }

    /**
     * @return string
     */
    public function cacheKey(): string
    {
        return 'sitemap.' . (string) $this->sitemapId;
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 3600;
    }
}
