<?php
namespace KiwiSuite\Cms\PageType;

use KiwiSuite\Admin\Schema\Form\Elements\Form;
use KiwiSuite\Contract\Schema\SchemaInterface;
use KiwiSuite\Contract\Schema\SchemaReceiverInterface;
use KiwiSuite\Contract\ServiceManager\NamedServiceInterface;
use KiwiSuite\Schema\Builder;
use KiwiSuite\Schema\Schema;

interface PageTypeInterface extends NamedServiceInterface, SchemaReceiverInterface
{
    public function label(): string;

    public function routing(): string;

    public function handle(): ?string;

    public function isRoot(): ?bool;

    public function allowedChildren(): ?array;

    public function middleware(): ?array;

    public function layout(): string;
}
