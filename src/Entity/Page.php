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
use KiwiSuite\Entity\Entity\Definition;
use KiwiSuite\Entity\Entity\DefinitionCollection;
use KiwiSuite\Entity\Entity\EntityInterface;
use KiwiSuite\Entity\Entity\EntityTrait;
use KiwiSuite\CommonTypes\Entity\UuidType;
use KiwiSuite\Entity\Type\TypeInterface;

final class Page implements EntityInterface
{
    use EntityTrait;

    private $id;
    private $sitemapId;
    private $locale;
    private $name;
    private $slug;
    private $publishedFrom;
    private $publishedUntil;
    private $status;
    private $updatedAt;
    private $createdAt;

    public function id(): UuidType
    {
        return $this->id;
    }

    public function sitemapId(): UuidType
    {
        return $this->sitemapId;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): ?string
    {
        return $this->slug;
    }

    public function publishedFrom(): ?DateTimeType
    {
        return $this->publishedFrom;
    }

    public function publishedUntil(): ?DateTimeType
    {
        return $this->publishedUntil;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function updatedAt(): DateTimeType
    {
        return $this->updatedAt;
    }

    public function createdAt(): DateTimeType
    {
        return $this->createdAt;
    }

    public function isOnline(): bool
    {
        if ($this->status() === "offline") {
            return false;
        }

        if ($this->publishedFrom() instanceof DateTimeType && $this->publishedFrom()->value()->getTimestamp() > time()) {
            return false;
        }

        if ($this->publishedUntil() instanceof DateTimeType && $this->publishedUntil()->value()->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('id', UuidType::class, false, true),
            new Definition('sitemapId', UuidType::class, false, true),
            new Definition('locale', TypeInterface::TYPE_STRING, false, true),
            new Definition('name', TypeInterface::TYPE_STRING, false, true),
            new Definition('slug', TypeInterface::TYPE_STRING, true, true),
            new Definition('publishedFrom', DateTimeType::class, true, true),
            new Definition('publishedUntil', DateTimeType::class, true, true),
            new Definition('status', TypeInterface::TYPE_STRING, false, true),
            new Definition('updatedAt', DateTimeType::class, false, true),
            new Definition('createdAt', DateTimeType::class, false, true),
        ]);
    }
}

