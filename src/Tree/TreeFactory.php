<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Strategy\StrategyInterface;
use Ixocreate\Cms\Tree\Mutatable\MutatableInterface;
use Ixocreate\Cms\Tree\Mutatable\MutatableSubManager;
use Ixocreate\Cms\Tree\Searchable\SearchableInterface;
use Ixocreate\Cms\Tree\Searchable\SearchableSubManager;

final class TreeFactory implements TreeFactoryInterface
{
    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var MutatableSubManager
     */
    private $mutatableSubManager;
    /**
     * @var SearchableSubManager
     */
    private $searchableSubManager;

    public function __construct(
        StrategyInterface $strategy,
        PageTypeSubManager $pageTypeSubManager,
        MutatableSubManager $mutatableSubManager,
        SearchableSubManager $searchableSubManager
    ) {
        $this->strategy = $strategy;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->mutatableSubManager = $mutatableSubManager;
        $this->searchableSubManager = $searchableSubManager;
    }

    /**
     * @return ContainerInterface
     */
    public function createRoot(): ContainerInterface
    {
        return $this->createContainer(
            $this->strategy->root(),
            new MutationCollection()
        );
    }

    /**
     * @param string $id
     * @param MutationCollection $mutationCollection
     * @return ItemInterface
     */
    public function createItem(string $id, MutationCollection $mutationCollection): ItemInterface
    {
        return new Item(
            $id,
            $mutationCollection,
            $this,
            $this->strategy
        );
    }

    /**
     * @param array $ids
     * @param MutationCollection $mutationCollection
     * @return ContainerInterface
     */
    public function createContainer(array $ids, MutationCollection $mutationCollection): ContainerInterface
    {
        return new Container(
            $ids,
            $mutationCollection,
            $this,
            $this->strategy
        );
    }

    /**
     * @param $searchable
     * @return SearchableInterface
     */
    public function searchable($searchable): SearchableInterface
    {
        return $this->searchableSubManager->get($searchable);
    }

    /**
     * @param $mutatable
     * @return MutatableInterface
     */
    public function mutatable($mutatable): MutatableInterface
    {
        return $this->mutatableSubManager->get($mutatable);
    }

    /**
     * @param string $pageType
     * @return PageTypeInterface
     */
    public function pageType(string $pageType): PageTypeInterface
    {
        return $this->pageTypeSubManager->get($pageType);
    }
}
