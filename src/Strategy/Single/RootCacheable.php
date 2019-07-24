<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Single;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Ixocreate\Cache\CacheableInterface;

final class RootCacheable implements CacheableInterface
{


    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @return mixed
     */
    public function uncachedResult()
    {
        $root = [];
        $sql = "SELECT id FROM cms_sitemap WHERE parentId = IS NULL ORDER BY nestedLeft";
        $rm = new ResultSetMapping();
        $rm->addScalarResult('id', 'id', 'string');
        $query = $this->entityManager->createNativeQuery($sql, $rm);
        $result = $query->execute(null, AbstractQuery::HYDRATE_SCALAR);
        foreach ($result as $item) {
            $root[] = $item['id'];
        }

        return $root;
    }

    /**
     * @return string
     */
    public function cacheName(): string
    {

    }

    /**
     * @return string
     */
    public function cacheKey(): string
    {
        return 'structure.root';
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 2592000;
    }
}
