<?php
namespace KiwiSuite\Cms\Loader;

use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Entity\Entity\EntityCollection;

final class DatabasePageLoader implements PageLoaderInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var EntityCollection
     */
    private $collection;

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
        $this->init();

        if (!$this->collection->has($pageId)){
            return null;
        }

        return $this->collection->get($pageId);
    }

    /**
     *
     */
    private function init(): void
    {
        if ($this->collection instanceof EntityCollection) {
            return;
        }

        $result = $this->pageRepository->findAll();
        $this->collection = new EntityCollection($result, 'id');
    }
}