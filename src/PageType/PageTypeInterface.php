<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\PageType;

use Ixocreate\Contract\Schema\SchemaReceiverInterface;
use Ixocreate\Contract\ServiceManager\NamedServiceInterface;

interface PageTypeInterface extends NamedServiceInterface, SchemaReceiverInterface
{
    public function label(): string;

    public function routing(): string;

    public function handle(): ?string;

    public function isRoot(): ?bool;

    public function allowedChildren(): ?array;

    public function middleware(): ?array;

    public function layout(): string;

    public function terminal(): bool;
}
