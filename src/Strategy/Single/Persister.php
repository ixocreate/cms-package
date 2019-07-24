<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Single;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Ixocreate\Cache\CacheInterface;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Strategy\PersisterInterface;
use SplFixedArray;

final class Persister implements PersisterInterface
{

    /**
     * @param EntityManagerInterface $entityManager
     * @param CacheInterface $cache
     */
    public function __construct(EntityManagerInterface $entityManager, CacheInterface $cache)
    {
    }

    public function persistSitemap(): void
    {

    }

    public function persistNavigation(Page $page): void
    {
    }

    public function persistPage(Page $page): void
    {
    }
}
