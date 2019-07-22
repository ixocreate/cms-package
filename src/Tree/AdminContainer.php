<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use JsonSerializable;

final class AdminContainer extends AbstractContainer implements JsonSerializable
{

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $items = [];

        foreach ($this as $item) {
            $items[] = $item;
        }

        return $items;
    }
}
