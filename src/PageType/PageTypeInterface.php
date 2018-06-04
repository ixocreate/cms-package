<?php
namespace KiwiSuite\Cms\PageType;

use KiwiSuite\Admin\Schema\Form\Elements\Form;

interface PageTypeInterface
{
    public static function name(): string;

    public function label(): string;

    public function routing(): string;

    public function handle(): ?string;

    public function isRoot(): ?bool;

    public function allowedChildren(): ?array;

    public function middleware(): ?array;

    public function layout(): string;

    public function elements(Form $form): array;
}
