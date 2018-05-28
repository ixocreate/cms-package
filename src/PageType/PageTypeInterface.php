<?php
namespace KiwiSuite\Cms\PageType;

interface PageTypeInterface
{
    public static function name(): string;

    public function label(): string;

    public function handle(): ?string;

    public function isRoot(): ?bool;

    public function allowedChildren(): ?array;

    public function allowedParents(): ?array;

    public function middleware(): ?array;

    public function layout(): string;

    public function elements(): array;
}
