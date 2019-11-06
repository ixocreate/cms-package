<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Structure;

final class Structure
{
    /**
     * @var array
     */
    private $ids = [];

    /**
     * @var StructureLoader
     */
    private $structureLoader;

    public function __construct(array $ids, StructureLoader $structureLoader)
    {
        $this->ids = $ids;
        $this->structureLoader = $structureLoader;
    }

    public function structure(): array
    {
        $structureItems = [];
        foreach ($this->ids as $id) {
            $structureItems[] = new StructureItem(
                $id,
                $this->structureLoader
            );
        }

        return $structureItems;
    }

    public function structureLoader(): StructureLoader
    {
        return $this->structureLoader;
    }
}
