<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Router\Tree;

use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Strategy\StrategyInterface;
use Ixocreate\Cms\Tree\ContainerInterface;
use Ixocreate\Cms\Tree\ItemInterface;
use Ixocreate\Cms\Tree\Mutatable\MutatableInterface;
use Ixocreate\Cms\Tree\Mutatable\MutatableSubManager;
use Ixocreate\Cms\Tree\MutationCollection;
use Ixocreate\Cms\Tree\Searchable\SearchableInterface;
use Ixocreate\Cms\Tree\Searchable\SearchableSubManager;
use Ixocreate\Cms\Tree\TreeFactoryInterface;

final class RoutingTreeFactory implements TreeFactoryInterface
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
    /**
     * @var ReplacementManager
     */
    private $replacementManager;

    public function __construct(
        StrategyInterface $strategy,
        ReplacementManager $replacementManager,
        PageTypeSubManager $pageTypeSubManager,
        MutatableSubManager $mutatableSubManager,
        SearchableSubManager $searchableSubManager
    ) {
        $this->strategy = $strategy;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->mutatableSubManager = $mutatableSubManager;
        $this->searchableSubManager = $searchableSubManager;
        $this->replacementManager = $replacementManager;
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
        return new RoutingItem(
            $id,
            $mutationCollection,
            $this,
            $this->strategy,
            $this->replacementManager
        );
    }

    /**
     * @param array $ids
     * @param MutationCollection $mutationCollection
     * @return ContainerInterface
     */
    public function createContainer(array $ids, MutationCollection $mutationCollection): ContainerInterface
    {
        return new RoutingContainer(
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
