<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Strategy\Admin\Strategy;
use Ixocreate\Cms\Tree\Mutatable\MutatableInterface;
use Ixocreate\Cms\Tree\Mutatable\MutatableSubManager;
use Ixocreate\Cms\Tree\Searchable\SearchableInterface;
use Ixocreate\Cms\Tree\Searchable\SearchableSubManager;
use Ixocreate\Intl\LocaleManager;

final class AdminTreeFactory implements TreeFactoryInterface
{
    /**
     * @var Strategy
     */
    private $strategy;
    /**
     * @var LocaleManager
     */
    private $localeManager;
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
        Strategy $strategy,
        LocaleManager $localeManager,
        PageTypeSubManager $pageTypeSubManager,
        MutatableSubManager $mutatableSubManager,
        SearchableSubManager $searchableSubManager
    ) {
        $this->strategy = $strategy;
        $this->localeManager = $localeManager;
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
        return new AdminItem(
            $id,
            $mutationCollection,
            $this,
            $this->strategy,
            $this->localeManager
        );
    }

    /**
     * @param array $ids
     * @param MutationCollection $mutationCollection
     * @return ContainerInterface
     */
    public function createContainer(array $ids, MutationCollection $mutationCollection): ContainerInterface
    {
        return new AdminContainer(
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
