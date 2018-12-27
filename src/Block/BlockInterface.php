<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Block;

use Ixocreate\Contract\Schema\SchemaReceiverInterface;
use Ixocreate\Contract\ServiceManager\NamedServiceInterface;

interface BlockInterface extends NamedServiceInterface, SchemaReceiverInterface
{
    public function template(): string;

    public function label(): string;
}
