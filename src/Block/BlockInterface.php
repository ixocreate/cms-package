<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Block;

use Ixocreate\Application\ServiceManager\NamedServiceInterface;
use Ixocreate\Schema\SchemaReceiverInterface;

interface BlockInterface extends NamedServiceInterface, SchemaReceiverInterface
{
    public function template(): string;

    public function label(): string;
}
