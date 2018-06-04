<?php
declare(strict_types=1);

namespace KiwiSuite\Cms\Block;


use KiwiSuite\Admin\Schema\Form\Elements\ElementGroup;

interface BlockInterface
{
    public static function name(): string;

    public function label(): string;

    public function elements(ElementGroup $elementGroup);
}
