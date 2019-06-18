<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Tree\Structure;

final class StructureStore
{
    /**
     * @var array
     */
    private $tree;

    public function __construct(array $tree)
    {
        $this->tree = $tree;
    }

    /**
     * @return Structure
     */
    public function structure(): Structure
    {
        return new Structure(
            $this,
            \array_map(function ($value) {
                return (string) $value;
            }, \array_keys($this->tree)),
            0,
            null
        );
    }

    /**
     * @param string $requestedKey
     * @throws \Exception
     * @return array
     */
    public function item(string $requestedKey): array
    {
        $parts = \explode(
            '.',
            \str_replace('.', '.children.', $requestedKey)
        );

        $requestedItem =& $this->tree;
        foreach ($parts as $key) {
            $requestedItem =& $requestedItem[$key];
        }

        $children = [];
        foreach (\array_keys($requestedItem['children']) as $childKey) {
            $children[] = $requestedKey . '.' . (string) $childKey;
        }

        return [
            'sitemap' => $requestedItem['sitemap'],
            'pages' => $requestedItem['pages'],
            'children' => $children,
            'navigation' => $requestedItem['navigation'],
        ];
    }
}
