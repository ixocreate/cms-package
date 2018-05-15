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
use KiwiSuite\Database\ORM\Metadata\AbstractMetadata;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;
use KiwiSuite\CommonTypes\Entity\UuidType;

final class SitemapMetadata extends AbstractMetadata
{

    protected function buildMetadata(): void
    {
        $builder = $this->getBuilder();
        $builder->setTable('cms_sitemap');

        $this->setFieldBuilder('id',
            $builder->createField('id', UuidType::class)
                ->makePrimaryKey()
        )->build();

        $this->setFieldBuilder('nestedLeft',
            $builder->createField('nestedLeft', Type::INTEGER)
        )->build();

        $this->setFieldBuilder('nestedRight',
            $builder->createField('nestedRight', Type::INTEGER)
        )->build();

        $this->setFieldBuilder('parentId',
            $builder->createField('parentId', UuidType::class)
        )->build();

        $this->setFieldBuilder('pageType',
            $builder->createField('pageType', Type::STRING)
        )->build();

        $this->setFieldBuilder('handle',
            $builder->createField('handle', Type::STRING)
        )->build();
    }
    
    public function id(): FieldBuilder
    {
        return $this->getField('id');
    }

    public function nestedLeft(): FieldBuilder
    {
        return $this->getField('nestedLeft');
    }

    public function nestedRight(): FieldBuilder
    {
        return $this->getField('nestedRight');
    }

    public function parentId(): FieldBuilder
    {
        return $this->getField('parentId');
    }

    public function pageType(): FieldBuilder
    {
        return $this->getField('pageType');
    }

    public function handle(): FieldBuilder
    {
        return $this->getField('handle');
    }
}

