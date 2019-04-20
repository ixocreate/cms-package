<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\PageType;

use Ixocreate\Schema\SchemaProviderInterface;
use Ixocreate\ServiceManager\NamedServiceInterface;

interface PageTypeInterface extends NamedServiceInterface, SchemaProviderInterface
{
    public function label(): string;

    public function allowedChildren(): ?array;

    public function template(): string;
}
