<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree\Filter;

use Ixocreate\Cms\Tree\ItemInterface;
use Ixocreate\ServiceManager\NamedServiceInterface;

interface FilterInterface extends NamedServiceInterface
{
    public function filter(ItemInterface $item, array $params = []): bool;
}
