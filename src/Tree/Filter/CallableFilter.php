<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree\Filter;

use Ixocreate\Cms\Tree\ItemInterface;

final class CallableFilter implements FilterInterface
{
    public function filter(ItemInterface $item, array $params = []): bool
    {
        if (empty($params['callable']) || !\is_callable($params['callable'])) {
            return false;
        }

        $callable = $params['callable'];
        return (bool) $callable($item);
    }

    public static function serviceName(): string
    {
        return 'callable';
    }
}
