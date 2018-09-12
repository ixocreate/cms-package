<?php
namespace KiwiSuite\Cms\Site\Admin;

use KiwiSuite\Entity\Collection\AbstractCollection;

final class ItemCollection extends AbstractCollection implements \JsonSerializable
{
    public function __construct(array $items = [])
    {
        $items = \array_values($items);
        $items = (function (Item ...$item) {
            return $item;
        })(...$items);

        parent::__construct($items, function (Item $item) {
            return $item->sitemap()->id();
        });
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_values($this->all());
    }
}