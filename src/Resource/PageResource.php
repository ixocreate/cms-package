<?php
namespace KiwiSuite\Cms\Resource;

use KiwiSuite\Admin\Resource\ResourceInterface;
use KiwiSuite\Admin\Resource\ResourceTrait;
use KiwiSuite\Cms\Action\Page\IndexAction;
use KiwiSuite\Cms\Message\CreatePage;
use KiwiSuite\Cms\Repository\PageRepository;

final class PageResource implements ResourceInterface
{
    use ResourceTrait;

    public function createMessage(): string
    {
        return CreatePage::class;
    }

    public function indexAction(): ?string
    {
        return IndexAction::class;
    }

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
