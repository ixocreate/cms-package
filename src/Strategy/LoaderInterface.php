<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy;

interface LoaderInterface
{
    /**
     * @return string[]
     */
    public function root(): array;

    /**
     * @param string $id
     * @return StructureInterface
     */
    public function get(string $id): StructureInterface;
}
