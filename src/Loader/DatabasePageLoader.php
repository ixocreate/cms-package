<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Loader;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Repository\PageRepository;

final class DatabasePageLoader implements PageLoaderInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @param PageRepository $pageRepository
     */
    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param string $pageId
     * @return Page|null
     */
    public function receivePage(string $pageId): ?Page
    {
        return $this->pageRepository->find($pageId);
    }
}
