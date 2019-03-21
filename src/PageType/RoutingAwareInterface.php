<?php

declare(strict_types=1);

namespace Ixocreate\Cms\PageType;

interface RoutingAwareInterface
{
    public function routing(): string;
}