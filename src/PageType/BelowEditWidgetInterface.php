<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\PageType;

use Ixocreate\Admin\UserInterface;
use Ixocreate\Admin\Widget\WidgetCollectorInterface;
use Ixocreate\Cms\Entity\Page;

interface BelowEditWidgetInterface
{
    public function receiveBelowEditWidgets(UserInterface $user, WidgetCollectorInterface $widgetCollector, Page $page): void;
}
