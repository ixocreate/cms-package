<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\PageType;

use Ixocreate\Contract\Schema\SchemaProviderInterface;
use Ixocreate\Contract\ServiceManager\NamedServiceInterface;

interface PageTypeInterface extends NamedServiceInterface, SchemaProviderInterface
{
    public function label(): string;

    public function allowedChildren(): ?array;

    public function template(): string;
}
