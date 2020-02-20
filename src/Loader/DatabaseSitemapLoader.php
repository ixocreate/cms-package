<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Loader;

use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Repository\SitemapRepository;

final class DatabaseSitemapLoader implements SitemapLoaderInterface
{
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

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
     * @param string $sitemapId
     * @return Sitemap|null
     */
    public function receiveSitemap(string $sitemapId): ?Sitemap
    {
        return $this->sitemapRepository->find($sitemapId);
    }

    public function receiveHandles(): array
    {
        if (!\is_array($this->handles)) {
            $this->handles = $this->sitemapRepository->receiveUsedHandles();
        }

        return $this->handles;
    }
}
