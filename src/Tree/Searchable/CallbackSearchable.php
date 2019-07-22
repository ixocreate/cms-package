<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree\Searchable;

use Ixocreate\Cms\Tree\ItemInterface;

final class CallbackSearchable implements SearchableInterface
{
    /**
     * @param ItemInterface $item
     * @param array $params
     * @return bool
     */
    public function search(ItemInterface $item, array $params = []): bool
    {
        $callable = $params['callback'];

        return $callable($item, $params);
    }
}
