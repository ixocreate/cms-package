<?php
namespace KiwiSuite\Cms\Resource;

use KiwiSuite\Admin\Resource\ResourceInterface;
use KiwiSuite\Admin\Resource\ResourceTrait;
use KiwiSuite\Cms\Repository\PageRepository;

final class PageResource implements ResourceInterface
{
    use ResourceTrait;

    public static function name(): string
    {
        return "page";
    }

    public function repository(): string
    {
        return PageRepository::class;
    }

    public function icon(): string
    {
        return "fa";
    }
}
