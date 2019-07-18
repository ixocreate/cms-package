<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\Tree\Mutatable\MutatableInterface;
use Ixocreate\Cms\Tree\Searchable\SearchableInterface;

interface TreeFactoryInterface
{
    /**
     * @return ContainerInterface
     */
    public function createRoot(): ContainerInterface;

    /**
     * @param string $id
     * @param MutationCollection $mutationCollection
     * @return ItemInterface
     */
    public function createItem(string $id, MutationCollection $mutationCollection): ItemInterface;

    /**
     * @param array $ids
     * @param MutationCollection $mutationCollection
     * @return ContainerInterface
     */
    public function createContainer(array $ids, MutationCollection $mutationCollection): ContainerInterface;

    /**
     * @param $searchable
     * @return SearchableInterface
     */
    public function searchable($searchable): SearchableInterface;

    /**
     * @param $mutatable
     * @return MutatableInterface
     */
    public function mutatable($mutatable): MutatableInterface;

    /**
     * @param string $pageType
     * @return PageTypeInterface
     */
    public function pageType(string $pageType): PageTypeInterface;
}
