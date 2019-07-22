<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Essential;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Strategy\StrategyInterface;
use Ixocreate\Cms\Strategy\StructureInterface;

final class Strategy implements StrategyInterface
{
    public const CACHE_KEY = 'strategy.essential';

    /**
     * @var Loader
     */
    private $loader;
    /**
     * @var Persister
     */
    private $persister;

    public function __construct(Loader $loader, Persister $persister)
    {
        $this->loader = $loader;
        $this->persister = $persister;
    }

    /**
     * @return string[]
     */
    public function root(): array
    {
        return $this->loader->root();
    }

    /**
     * @param string $id
     * @return StructureInterface
     */
    public function get(string $id): StructureInterface
    {
        return $this->loader->get($id);
    }

    public function persistSitemap(): void
    {
        $this->persister->persistSitemap();
    }

    public function persistPage(Page $page): void
    {
        $this->persister->persistPage($page);
    }

    public function persistNavigation(Page $page): void
    {
        $this->persister->persistNavigation($page);
    }
}
