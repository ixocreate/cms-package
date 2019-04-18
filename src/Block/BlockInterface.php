<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Block;

use Ixocreate\Package\Schema\SchemaReceiverInterface;
use Ixocreate\ServiceManager\NamedServiceInterface;

interface BlockInterface extends NamedServiceInterface, SchemaReceiverInterface
{
    public function template(): string;

    public function label(): string;
}
