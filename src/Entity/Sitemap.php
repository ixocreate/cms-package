<?php

/**
 * kiwi-suite/cms (https://github.com/kiwi-suite/cms)
 *
 * @package kiwi-suite/cms
 * @see https://github.com/kiwi-suite/cms
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace KiwiSuite\Cms\Entity;

use KiwiSuite\Database\Tree\NodeInterface;
use KiwiSuite\Entity\Entity\Definition;
use KiwiSuite\Entity\Entity\DefinitionCollection;
use KiwiSuite\Entity\Entity\EntityTrait;
use KiwiSuite\CommonTypes\Entity\UuidType;
use KiwiSuite\Entity\Type\TypeInterface;

final class Sitemap implements NodeInterface
{
    use EntityTrait;

    private $id;
    private $parentId;
    private $nestedLeft;
    private $nestedRight;
    private $pageType;
    private $handle;

    public function id(): UuidType
    {
        return $this->id;
    }

    public function parentId(): ?UuidType
    {
        return $this->parentId;
    }

    public function nestedLeft(): ?int
    {
        return $this->nestedLeft;
    }

    public function nestedRight(): ?int
    {
        return $this->nestedRight;
    }

    public function pageType(): string
    {
        return $this->pageType;
    }

    public function handle(): ?string
    {
        return $this->handle;
    }

    protected function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('id', UuidType::class, false, true),
            new Definition('parentId', UuidType::class, true, true),
            new Definition('nestedLeft', TypeInterface::TYPE_INT, true, true),
            new Definition('nestedRight', TypeInterface::TYPE_INT, true, true),
            new Definition('pageType', TypeInterface::TYPE_STRING, false, true),
            new Definition('handle', TypeInterface::TYPE_STRING, true, true),
        ]);
    }

    public function right(): ?int
    {
        return $this->nestedRight();
    }

    public function left(): ?int
    {
        return $this->nestedLeft();
    }


    public function leftParameterName(): string
    {
        return 'nestedLeft';
    }

    public function rightParameterName(): string
    {
        return 'nestedRight';
    }

    public function parentIdParameterName(): string
    {
        return 'parentId';
    }

    public function idName(): string
    {
        return 'id';
    }
}

