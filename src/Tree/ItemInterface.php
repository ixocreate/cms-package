<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\PageType\PageTypeInterface;

interface ItemInterface extends ContainerInterface
{
    public function below(): ContainerInterface;

    public function level(): int;

    public function parent(): ?ItemInterface;

    public function pageType(): PageTypeInterface;

    public function handle(): ?string;
}
