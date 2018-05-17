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

use KiwiSuite\CommonTypes\Entity\DateTimeType;
use KiwiSuite\Database\Tree\NodeInterface;
use KiwiSuite\Entity\Entity\Definition;
use KiwiSuite\Entity\Entity\DefinitionCollection;
use KiwiSuite\Entity\Entity\EntityTrait;
use KiwiSuite\CommonTypes\Entity\UuidType;
use KiwiSuite\Entity\Type\TypeInterface;

final class PageVersion implements NodeInterface
{
    use EntityTrait;

    private $id;
    private $pageId;
    private $content;
    private $createdBy;
    private $approvedAt;
    private $createdAt;

    public function id(): UuidType
    {
        return $this->id;
    }

    public function pageId(): UuidType
    {
        return $this->pageId;
    }

    public function content()
    {
        return $this->content;
    }

    public function createdBy(): UuidType
    {
        return $this->createdBy;
    }

    public function approvedAt(): ?DateTimeType
    {
        return $this->approvedAt;
    }

    public function createdAt(): DateTimeType
    {
        return $this->createdAt;
    }

    protected function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('id', UuidType::class, false, true),
            new Definition('pageId', UuidType::class, false, true),
            new Definition('content', TypeInterface::TYPE_ARRAY, true, true),
            new Definition('createdBy', UuidType::class, false, true),
            new Definition('approvedAt', TypeInterface::TYPE_STRING, true, true),
            new Definition('createdAt', DateTimeType::class, false, true),
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
}

