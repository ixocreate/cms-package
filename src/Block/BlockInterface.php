<?php
declare(strict_types=1);

namespace KiwiSuite\Cms\Block;

interface BlockInterface
{
    public function name(): string;

    public function label(): string;

    public function elements(): array;
}
