<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Database;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Strategy\StrategyInterface;
use Ixocreate\Cms\Strategy\StructureInterface;

final class Strategy implements StrategyInterface
{
    /**
     * @var Loader
     */
    private $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
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
    }

    public function persistPage(Page $page): void
    {
    }

    public function persistNavigation(Page $page): void
    {
    }
}
