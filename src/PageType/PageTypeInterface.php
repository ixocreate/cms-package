<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\PageType;

use Ixocreate\Application\ServiceManager\NamedServiceInterface;
use Ixocreate\Schema\SchemaProviderInterface;

interface PageTypeInterface extends NamedServiceInterface, SchemaProviderInterface
{
    public function label(): string;

    public function allowedChildren(): ?array;

    public function template(): string;
}
