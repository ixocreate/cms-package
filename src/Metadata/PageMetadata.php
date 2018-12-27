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

namespace Ixocreate\Cms\Metadata;

use Doctrine\DBAL\Types\Type;
use Ixocreate\CommonTypes\Entity\DateTimeType;
use Ixocreate\Database\ORM\Metadata\AbstractMetadata;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;
use Ixocreate\CommonTypes\Entity\UuidType;

final class PageMetadata extends AbstractMetadata
{

    protected function buildMetadata(): void
    {
        $builder = $this->getBuilder();
        $builder->setTable('cms_page');

        $this->setFieldBuilder('id',
            $builder->createField('id', UuidType::class)
                ->makePrimaryKey()
        )->build();

        $this->setFieldBuilder('sitemapId',
            $builder->createField('sitemapId', UuidType::class)
        )->build();

        $this->setFieldBuilder('locale',
            $builder->createField('locale', Type::STRING)
        )->build();

        $this->setFieldBuilder('name',
            $builder->createField('name', Type::STRING)
        )->build();

        $this->setFieldBuilder('slug',
            $builder->createField('slug', Type::STRING)
        )->build();

        $this->setFieldBuilder('publishedFrom',
            $builder->createField('publishedFrom', DateTimeType::class)
        )->build();

        $this->setFieldBuilder('publishedUntil',
            $builder->createField('publishedUntil', DateTimeType::class)
        )->build();

        $this->setFieldBuilder('status',
            $builder->createField('status', Type::STRING)
        )->build();

        $this->setFieldBuilder('updatedAt',
            $builder->createField('updatedAt', DateTimeType::class)
        )->build();

        $this->setFieldBuilder('createdAt',
            $builder->createField('createdAt', DateTimeType::class)
        )->build();

        $this->setFieldBuilder('releasedAt',
            $builder->createField('releasedAt', DateTimeType::class)
        )->build();
    }
    
    public function id(): FieldBuilder
    {
        return $this->getField('id');
    }

    public function sitemapId(): FieldBuilder
    {
        return $this->getField('sitemapId');
    }

    public function locale(): FieldBuilder
    {
        return $this->getField('locale');
    }

    public function name(): FieldBuilder
    {
        return $this->getField('name');
    }

    public function slug(): FieldBuilder
    {
        return $this->getField('slug');
    }

    public function publishedFrom(): FieldBuilder
    {
        return $this->getField('publishedFrom');
    }

    public function publishedUntil(): FieldBuilder
    {
        return $this->getField('publishedUntil');
    }

    public function status(): FieldBuilder
    {
        return $this->getField('status');
    }

    public function updatedAt(): FieldBuilder
    {
        return $this->getField('updatedAt');
    }

    public function createdAt(): FieldBuilder
    {
        return $this->getField('createdAt');
    }

    public function releasedAt(): FieldBuilder
    {
        return $this->getField('releasedAt');
    }
}

