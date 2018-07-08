<?php
declare(strict_types=1);

namespace KiwiSuite\Cms\Block;

use KiwiSuite\Contract\Schema\SchemaInterface;
use KiwiSuite\Contract\ServiceManager\NamedServiceInterface;
use KiwiSuite\Schema\Builder;

interface BlockInterface extends NamedServiceInterface
{
    public function template(): string;

    public function label(): string;

    public function schema(Builder $builder): SchemaInterface;
}
