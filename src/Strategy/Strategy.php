<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy;

use Ixocreate\Cms\Entity\Page;

final class Strategy implements StrategyInterface
{
    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * Strategy constructor.
     * @param StrategyInterface $strategy
     */
    public function __construct(StrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @return string[]
     */
    public function root(): array
    {
        return $this->strategy->root();
    }

    /**
     * @param string $id
     * @return StructureInterface
     */
    public function get(string $id): StructureInterface
    {
        return $this->strategy->get($id);
    }

    /**
     *
     */
    public function persistSitemap(): void
    {
        $this->strategy->persistSitemap();
    }

    /**
     * @param Page $page
     */
    public function persistPage(Page $page): void
    {
        $this->strategy->persistPage($page);
    }

    /**
     * @param Page $page
     */
    public function persistNavigation(Page $page): void
    {
        $this->strategy->persistNavigation($page);
    }
}
