<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree\Mutatable;

use Ixocreate\Cms\Tree\ItemInterface;

final class CallbackMutatable implements MutatableInterface
{

    /**
     * @param ItemInterface $item
     * @param array $params
     * @return ItemInterface
     */
    public function mutate(ItemInterface $item, array $params = []): ItemInterface
    {
        $callable = $params['callback'];

        return $callable($item, $params);
    }
}
