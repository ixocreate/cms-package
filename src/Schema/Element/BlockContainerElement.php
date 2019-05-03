<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Schema\Element;

use Ixocreate\Cms\Block\BlockInterface;
use Ixocreate\Cms\Block\BlockSubManager;
use Ixocreate\Cms\Block\NameExpressionInterface;
use Ixocreate\Cms\Schema\Type\BlockContainerType;
use Ixocreate\Schema\Builder\Builder;
use Ixocreate\Schema\Element\AbstractGroup;
use Ixocreate\Schema\Element\ElementInterface;
use Ixocreate\Schema\Element\GroupElement;
use Ixocreate\Schema\SchemaInterface;
use Ixocreate\Schema\Type\Type;
use Ixocreate\Schema\Type\TypeInterface;

final class BlockContainerElement extends AbstractGroup
{
    private $blocks = [];

    /**
     * @var BlockSubManager
     */
    private $blockSubManager;

    /**
     * @var null
     */
    protected $limit = null;

    /**
     * @var Builder
     */
    private $builder;

    public function __construct(BlockSubManager $blockSubManager, Builder $builder)
    {
        $this->blockSubManager = $blockSubManager;
        $this->builder = $builder;
    }

    public function type(): string
    {
        return BlockContainerType::class;
    }

    public function inputType(): string
    {
        return 'blockContainer';
    }

    public static function serviceName(): string
    {
        return 'blockContainer';
    }

    public function withBlocks(array $blocks): BlockContainerElement
    {
        $element = clone $this;
        $element->blocks = $this->parseBlockOption($blocks);

        foreach ($element->blocks as $block) {
            $element = $element->addBlockElement($block);
        }

        return $element;
    }

    public function blocks(): array
    {
        return $this->blocks;
    }

    /**
     * @return int|null
     */
    public function limit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     * @return BlockContainerElement
     */
    public function withLimit(?int $limit): BlockContainerElement
    {
        $group = $this;
        $group->limit = $limit;

        return $group;
    }

    /**
     * @param ElementInterface $element
     * @throws \Exception
     * @return SchemaInterface
     */
    public function withAddedElement(ElementInterface $element): SchemaInterface
    {
        if (!($element instanceof GroupElement)) {
            throw new \Exception("Element must be a GroupElement");
        }
        return parent::withAddedElement($element);
    }

    public function jsonSerialize()
    {
        $array = parent::jsonSerialize();
        $array['blocks'] = $this->blocks();
        $array['limit'] = $this->limit();

        return $array;
    }

    public function transform($data): TypeInterface
    {
        return Type::create($data, BlockContainerType::class, ['schema' => $this, 'blocks' => $this->blocks()]);
    }

    /**
     * @param array $blocks
     * @return array
     */
    private function parseBlockOption(array $blocks): array
    {
        //TODO duplicate code!!!
        $parsedBlocks = [];

        foreach ($blocks as $blockName) {
            if (\mb_strpos($blockName, '*') === false) {
                if (\array_key_exists(
                    $blockName,
                    $this->blockSubManager->getServiceManagerConfig()->getNamedServices()
                )) {
                    $parsedBlocks[] = $blockName;
                }
                continue;
            }

            $beginningPart = \mb_substr($blockName, 0, \mb_strpos($blockName, '*'));

            foreach (\array_keys($this->blockSubManager->getServiceManagerConfig()->getNamedServices()) as $mappingBlock) {
                if (\mb_substr($mappingBlock, 0, \mb_strlen($beginningPart)) === $beginningPart) {
                    $parsedBlocks[] = $mappingBlock;
                }
            }
        }

        return $parsedBlocks;
    }

    private function addBlockElement(string $block): ElementInterface
    {
        /** @var BlockInterface $blockObj */
        $blockObj = $this->blockSubManager->get($block);

        $schema = $blockObj->receiveSchema($this->builder);

        /** @var GroupElement $group */
        $group = $this->builder->create(GroupElement::class, $block);
        $group = $group->withLabel($blockObj->label());
        $group = $group->withElements($schema->elements());

        if ($blockObj instanceof NameExpressionInterface) {
            $group = $group->withNameExpression($blockObj->nameExpression());
        }

        return $this->withAddedElement($group);
    }
}
