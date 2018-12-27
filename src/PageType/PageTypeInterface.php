<?php
namespace Ixocreate\Cms\PageType;

use Ixocreate\Admin\Schema\Form\Elements\Form;
use Ixocreate\Contract\Schema\SchemaInterface;
use Ixocreate\Contract\Schema\SchemaReceiverInterface;
use Ixocreate\Contract\ServiceManager\NamedServiceInterface;
use Ixocreate\Schema\Builder;
use Ixocreate\Schema\Schema;

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
