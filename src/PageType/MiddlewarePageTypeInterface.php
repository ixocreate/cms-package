<?php

declare(strict_types=1);

namespace Ixocreate\Cms\PageType;

interface MiddlewarePageTypeInterface
{
    public function middleware(): array;
}
