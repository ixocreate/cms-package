<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Cacheable;

use Doctrine\Common\Collections\Criteria;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\CommonTypes\Entity\SchemaType;
use Ixocreate\CommonTypes\Entity\UuidType;
use Ixocreate\Contract\Cache\CacheableInterface;
use Ixocreate\Entity\Type\Type;

final class PageContentCacheable implements CacheableInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

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

    public function withPageId($pageId): PageContentCacheable
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
            return $pageVersion->current()->content();
        }

        return Type::create([], SchemaType::serviceName());
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
        return 'pageContent.' . (string) $this->pageId;
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 3600;
    }
}
