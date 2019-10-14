<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Structure;

use Doctrine\ORM\EntityManagerInterface;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\SitemapRepository;

final class StructureBuilder
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var StructureLoader
     */
    private $structureLoader;

    public function __construct(EntityManagerInterface $master, PageTypeSubManager $pageTypeSubManager, SitemapRepository $sitemapRepository, StructureLoader $structureLoader)
    {
        $this->entityManager = $master;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
        $this->structureLoader = $structureLoader;
    }

    public function build(): Structure
    {
        $dql = 'SELECT node.id
FROM '.Sitemap::class.' AS node
WHERE node.parentId IS NULL
ORDER BY node.nestedLeft';
        $query = $this->sitemapRepository->createQuery($dql);
        $result = $query->getArrayResult();

        $root = [];
        foreach ($result as $item) {
            $root[] = (string) $item['id'];
        }

        return new Structure($root, $this->structureLoader);
    }
}
