<?php
declare(strict_types=1);

namespace KiwiSuite\Cms\Block;

use KiwiSuite\Contract\Schema\SchemaReceiverInterface;
use KiwiSuite\Contract\ServiceManager\NamedServiceInterface;

interface BlockInterface extends NamedServiceInterface, SchemaReceiverInterface
{
    public function template(): string;

    public function label(): string;
}
