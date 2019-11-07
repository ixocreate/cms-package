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
     * @var StructureLoaderInterface
     */
    private $structureLoader;

    public function __construct(array $ids, StructureLoaderInterface $structureLoader)
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

    public function structureLoader(): StructureLoaderInterface
    {
        return $this->structureLoader;
    }
}
