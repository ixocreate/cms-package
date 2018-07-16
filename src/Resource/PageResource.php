<?php

namespace KiwiSuite\Cms\Resource;

use KiwiSuite\Admin\Resource\DefaultAdminTrait;
use KiwiSuite\Cms\Action\Page\CreateAction;
use KiwiSuite\Cms\Action\Page\DeleteAction;
use KiwiSuite\Cms\Action\Page\IndexAction;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Contract\Resource\AdminAwareInterface;
use KiwiSuite\Contract\Schema\BuilderInterface;
use KiwiSuite\Contract\Schema\Listing\ListSchemaInterface;
use KiwiSuite\Contract\Schema\SchemaInterface;
use KiwiSuite\Schema\Elements\DateTimeElement;
use KiwiSuite\Schema\Elements\SectionElement;
use KiwiSuite\Schema\Elements\SelectElement;
use KiwiSuite\Schema\Elements\TextElement;
use KiwiSuite\Schema\Listing\ListSchema;
use KiwiSuite\Schema\Schema;

final class PageResource implements AdminAwareInterface
{
    use DefaultAdminTrait;

    public function label(): string
    {
        return "Page";
    }

    public static function serviceName(): string
    {
        return "page";
    }

    public function indexAction(): ?string
    {
        return IndexAction::class;
    }

    public function createAction(): ?string
    {
        return CreateAction::class;
    }

    public function deleteAction(): ?string
    {
        return DeleteAction::class;
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
        $schema = new Schema();
        /** @var SectionElement $segment */
        $section = $builder->create(SectionElement::class, "general")
            ->withAddedElement(
                $builder->create(TextElement::class, "name")
                    ->withLabel("Name")
            )
            ->withLabel("General")
            ->withIcon("fa-cog");
        $schema = $schema->withAddedElement($section);

        /** @var SectionElement $segment */
        $section = $builder->create(SectionElement::class, "scheduling")
            ->withAddedElement(
                $builder->create(DateTimeElement::class, "publishedFrom")
                    ->withLabel("Published From")
            )
            ->withAddedElement(
                $builder->create(DateTimeElement::class, "publishedUntil")
                    ->withLabel("Published Until")
            )
            ->withLabel("Scheduling")
            ->withIcon("fa-calendar");
        $schema = $schema->withAddedElement($section);

        /** @var SectionElement $segment */
        $section = $builder->create(SectionElement::class, "status")
            ->withAddedElement(
                $builder->create(SelectElement::class, "status")
                    ->withLabel("Status")
                    ->withOptions([
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ])
            )
            ->withLabel("Status")
            ->withIcon("fa-power-off");
        $schema = $schema->withAddedElement($section);

        return $schema;
    }

    /**
     * @return ListSchemaInterface
     */
    public function listSchema(): ListSchemaInterface
    {
        return new ListSchema();
    }

    public function repository(): string
    {
        return PageRepository::class;
    }
}
