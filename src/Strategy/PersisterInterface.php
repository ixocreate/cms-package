<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy;

use Ixocreate\Cms\Entity\Page;

interface PersisterInterface
{
    public function persistSitemap(): void;

    public function persistPage(Page $page): void;

    public function persistNavigation(Page $page): void;
}
