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

namespace KiwiSuite\Cms\Metadata;

use Doctrine\DBAL\Types\Type;
use KiwiSuite\CommonTypes\Entity\DateTimeType;
use KiwiSuite\Database\ORM\Metadata\AbstractMetadata;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;
use KiwiSuite\CommonTypes\Entity\UuidType;

final class PageVersionMetadata extends AbstractMetadata
{

    protected function buildMetadata(): void
    {
        $builder = $this->getBuilder();
        $builder->setTable('cms_page_version');

        $this->setFieldBuilder('id',
            $builder->createField('id', UuidType::class)
                ->makePrimaryKey()
        )->build();

        $this->setFieldBuilder('pageId',
            $builder->createField('pageId', UuidType::class)
        )->build();

        $this->setFieldBuilder('content',
            $builder->createField('content', Type::JSON)
        )->build();

        $this->setFieldBuilder('createdBy',
            $builder->createField('createdBy', UuidType::class)
        )->build();

        $this->setFieldBuilder('approvedAt',
            $builder->createField('approvedAt', DateTimeType::class)
        )->build();

        $this->setFieldBuilder('createdAt',
            $builder->createField('createdAt', DateTimeType::class)
        )->build();
    }
    
    public function id(): FieldBuilder
    {
        return $this->getField('id');
    }

    public function pageId(): FieldBuilder
    {
        return $this->getField('pageId');
    }

    public function content(): FieldBuilder
    {
        return $this->getField('content');
    }

    public function createdBy(): FieldBuilder
    {
        return $this->getField('createdBy');
    }

    public function approvedAt(): FieldBuilder
    {
        return $this->getField('approvedAt');
    }

    public function createdAt(): FieldBuilder
    {
        return $this->getField('createdAt');
    }
}

