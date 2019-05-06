<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Cacheable;

use Doctrine\Common\Collections\Criteria;
use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Schema\Type\UuidType;

final class PageVersionCacheable implements CacheableInterface
{
    /**
     * @var UuidType|string
     */
    private $pageId;

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * SitemapCacheable constructor.
     * @param PageVersionRepository $pageVersionRepository
     */
    public function __construct(PageVersionRepository $pageVersionRepository)
    {
        $this->pageVersionRepository = $pageVersionRepository;
    }

    public function withPageId($pageId): PageVersionCacheable
    {
        $cacheable = clone $this;
        $cacheable->pageId = $pageId;

        return $cacheable;
    }

    /**
     * @return mixed
     */
    public function uncachedResult()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('pageId', $this->pageId));
        $criteria->andWhere(Criteria::expr()->neq('approvedAt', null));
        $criteria->orderBy(['approvedAt' => 'DESC']);
        $criteria->setMaxResults(1);
        $pageVersion = $this->pageVersionRepository->matching($criteria);

        if ($pageVersion->count() > 0) {
            return $pageVersion->current();
        }

        return null;
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
        return 'pageVersion.' . (string) $this->pageId;
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 3600;
    }
}
