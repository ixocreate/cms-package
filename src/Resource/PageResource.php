<?php

namespace KiwiSuite\Cms\Resource;

use KiwiSuite\Admin\Resource\ResourceInterface;
use KiwiSuite\Admin\Resource\ResourceTrait;
use KiwiSuite\Admin\Schema\Form\Elements\DateTime;
use KiwiSuite\Admin\Schema\Form\Elements\ElementGroup;
use KiwiSuite\Admin\Schema\Form\Elements\Select;
use KiwiSuite\Admin\Schema\Form\Elements\Text;
use KiwiSuite\Admin\Schema\SchemaBuilder;
use KiwiSuite\Cms\Action\Page\IndexAction;
use KiwiSuite\Cms\Message\CreatePage;
use KiwiSuite\Cms\Repository\PageRepository;

final class PageResource implements ResourceInterface
{
    use ResourceTrait;

    public function createMessage(): string
    {
        return CreatePage::class;
    }

    public function indexAction(): ?string
    {
        return IndexAction::class;
    }

    public static function name(): string
    {
        return "page";
    }

    public function repository(): string
    {
        return PageRepository::class;
    }

    public function icon(): string
    {
        return "fa";
    }

    public function schema(SchemaBuilder $schemaBuilder): void
    {
        $schemaBuilder->setName("Page");
        $schemaBuilder->setNamePlural("Pages");
        $form = $schemaBuilder->getForm();
        $form->add(function (ElementGroup $elementGroup){
            $elementGroup->setName("general");
            $elementGroup->addWrapper("section");
            $elementGroup->setLabel("General");
            $elementGroup->addOption("icon", "fa fa-fw fa-cog");

            $elementGroup->add(function (Text $text) {
                $text->setName("name");
                $text->setLabel("Name");
                $text->setRequired(true);
            });
        });

        $form->add(function (ElementGroup $elementGroup){
            $elementGroup->setName("scheduling");
            $elementGroup->addWrapper("section");
            $elementGroup->setLabel("Scheduling");
            $elementGroup->addOption("icon", "fa fa-fw fa-cog");

            $elementGroup->add(function (DateTime $dateTime) {
                $dateTime->setName("publishedFrom");
                $dateTime->setLabel("Published From");
                $dateTime->addOption("placement", "left");
            });

            $elementGroup->add(function (DateTime $dateTime) {
                $dateTime->setName("publishedUntil");
                $dateTime->setLabel("Published Until");
                $dateTime->addOption("placement", "left");
            });
        });

        $form->add(function (ElementGroup $elementGroup){
            $elementGroup->setName("status");
            $elementGroup->addWrapper("section");
            $elementGroup->setLabel("Status");
            $elementGroup->addOption("icon", "fa fa-fw fa-power-off");

            $elementGroup->add(function (Select $select) {
                $select->setName("status");
                $select->setLabel("Status");
                $select->setSelectOptions([
                    [
                        'label' => 'Online',
                        'value' => 'online',
                    ],
                    [
                        'label' => 'Offline',
                        'value' => 'offline',
                    ],
                ]);
                $select->setRequired(true);
            });
        });
    }
}
