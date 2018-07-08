<?php

namespace KiwiSuite\Cms\Resource;

use KiwiSuite\Admin\Resource\DefaultAdminTrait;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Contract\Resource\AdminAwareInterface;
use KiwiSuite\Contract\Schema\BuilderInterface;
use KiwiSuite\Contract\Schema\SchemaInterface;
use KiwiSuite\Schema\Schema;

final class PageVersionResource implements AdminAwareInterface
{
    use DefaultAdminTrait;

    public function label(): string
    {
        return "Page Version";
    }

    public static function serviceName(): string
    {
        return "page-version";
    }

    /**
     * @param BuilderInterface $builder
     * @return SchemaInterface
     */
    public function createSchema(BuilderInterface $builder): SchemaInterface
    {
        return new Schema();
    }

    /**
     * @param BuilderInterface $builder
     * @return SchemaInterface
     */
    public function updateSchema(BuilderInterface $builder): SchemaInterface
    {
        return new Schema();
    }

    /**
     * @return array
     */
    public function listSchema(): array
    {
        return [];
    }

    public function repository(): string
    {
        return PageVersionRepository::class;
    }
}
