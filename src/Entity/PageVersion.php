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

namespace Ixocreate\Cms\Entity;

use Ixocreate\CommonTypes\Entity\DateTimeType;
use Ixocreate\CommonTypes\Entity\SchemaType;
use Ixocreate\Database\Tree\NodeInterface;
use Ixocreate\Entity\Entity\Definition;
use Ixocreate\Entity\Entity\DefinitionCollection;
use Ixocreate\Entity\Entity\EntityInterface;
use Ixocreate\Entity\Entity\EntityTrait;
use Ixocreate\CommonTypes\Entity\UuidType;
use Ixocreate\Entity\Type\TypeInterface;

final class PageVersion implements EntityInterface
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

    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('id', UuidType::class, false, true),
            new Definition('pageId', UuidType::class, false, true),
            new Definition('content', SchemaType::class, true, true),
            new Definition('createdBy', UuidType::class, false, true),
            new Definition('approvedAt', DateTimeType::class, true, true),
            new Definition('createdAt', DateTimeType::class, false, true),
        ]);
    }
}

