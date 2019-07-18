<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

final class MutationCollection
{
    private $filter = [];

    private $map = [];

    /**
     * @param string $mutable
     * @param array $params
     * @return MutationCollection
     */
    public function addFilter(string $mutable, array $params): MutationCollection
    {
        $collection = clone $this;
        $collection->filter[] = [
            $mutable,
            $params
        ];

        return $collection;
    }

    /**
     * @param string $mutable
     * @param array $params
     * @return MutationCollection
     */
    public function addMap(string $mutable, array $params): MutationCollection
    {
        $collection = clone $this;
        $collection->map[] = [
            $mutable,
            $params
        ];

        return $collection;
    }

    /**
     * @return array
     */
    public function filter(): array
    {
        return $this->filter;
    }

    /**
     * @return array
     */
    public function map(): array
    {
        return $this->map;
    }

    /**
     * @return bool
     */
    public function hasFilter(): bool
    {
        return \count($this->filter) > 0;
    }

    /**
     * @return bool
     */
    public function hasMap(): bool
    {
        return \count($this->map) > 0;
    }
}
