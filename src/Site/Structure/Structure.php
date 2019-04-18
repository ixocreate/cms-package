<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Site\Structure;

use Serializable;

final class Structure implements Serializable
{
    /**
     * @var array
     */
    private $structure = [];

    public function __construct(array $structure)
    {
        $this->structure = $structure;
    }

    public function structure(): array
    {
        $structureItems = [];
        foreach ($this->structure as $item) {
            $structureItems[] = new StructureItem(
                $item['sitemapId'],
                $item['handle'],
                $item['pages'],
                $item['navigation'],
                $item['children'],
                0
            );
        }

        return $structureItems;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return \serialize($this->structure);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->structure = \unserialize($serialized);
    }
}
