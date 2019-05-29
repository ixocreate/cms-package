<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Schema\Type;

use Doctrine\DBAL\Types\JsonType;
use Ixocreate\Application\ApplicationConfig;
use Ixocreate\Cms\Block\BlockInterface;
use Ixocreate\Cms\Block\BlockSubManager;
use Ixocreate\Schema\Builder\BuilderInterface;
use Ixocreate\Schema\Element\ElementInterface;
use Ixocreate\Schema\SchemaInterface;
use Ixocreate\Schema\Type\AbstractType;
use Ixocreate\Schema\Type\DatabaseTypeInterface;
use Ixocreate\Schema\Type\TransformableInterface;
use Ixocreate\Schema\Type\Type;
use Ixocreate\Schema\Type\TypeInterface;
use Ixocreate\Template\Renderer;

final class BlockType extends AbstractType implements DatabaseTypeInterface
{
    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var ApplicationConfig
     */
    private $applicationConfig;

    /**
     * @var BlockSubManager
     */
    private $blockSubManager;

    /**
     * @var BlockInterface
     */
    private $block;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $blockType;

    /**
     * BlockType constructor.
     *
     * @param BuilderInterface $builder
     * @param Renderer $renderer
     * @param ApplicationConfig $applicationConfig
     * @param BlockSubManager $blockSubManager
     */
    public function __construct(
        BuilderInterface $builder,
        Renderer $renderer,
        ApplicationConfig $applicationConfig,
        BlockSubManager $blockSubManager
    ) {
        $this->builder = $builder;
        $this->renderer = $renderer;
        $this->applicationConfig = $applicationConfig;
        $this->blockSubManager = $blockSubManager;
    }

    /**
     * @param $value
     * @param array $options
     * @throws \Exception
     * @return TypeInterface
     */
    public function create($value, array $options = []): TypeInterface
    {
        $type = clone $this;
        $type->options = $options;

        $type->blockType = $options['type'];
        if (empty($type->getSchema())) {
            throw new \Exception('Cant initialize without schema');
        }

        $type->value = $type->transform($value);

        return $type;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function transform($value)
    {
        if (!\is_array($value) || empty($value)) {
            return [];
        }

        $data = [];

        /** @var ElementInterface $element */
        foreach ($this->getSchema()->all() as $element) {
            $data[$element->name()] = null;
            if (\array_key_exists($element->name(), $value) && $value[$element->name()] !== null) {
                if ($element instanceof TransformableInterface) {
                    $data[$element->name()] = $element->transform($value[$element->name()]);
                } else {
                    $data[$element->name()] = Type::create($value[$element->name()], $element->type());
                }
            }
        }

        return $data;
    }

    /**
     * @return SchemaInterface
     */
    private function getSchema(): SchemaInterface
    {
        return $this->getBlock()->receiveSchema($this->builder);
    }

    /**
     * @return BlockInterface
     */
    private function getBlock(): BlockInterface
    {
        if ($this->block === null) {
            $this->block = $this->blockSubManager->get($this->blockType);
        }

        return $this->block;
    }

    public function __debugInfo()
    {
        return [
            'block' => $this->getBlock(),
            'value' => $this->value(),
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            $template = $this->getBlock()->template();
            if ($this->template) {
                $template = $this->template;
            }
            return $this->renderer->render($template, $this->value());
        } catch (\Throwable $e) {
            if (!$this->applicationConfig->isDevelopment()) {
                return '';
            }

            $errorResponse = 'Error in ' . $this->getBlock()->label() . " Block!\n\n" . $e;
            return $errorResponse;
        }
    }

    public function convertToDatabaseValue()
    {
        $values = [];

        foreach ($this->value() as $name => $val) {
            if ($val instanceof DatabaseTypeInterface) {
                $values[$name] = $val->convertToDatabaseValue();
                continue;
            }

            $values[$name] = $val;
        }

        return \array_merge(
            ['_type' => $this->getBlock()->serviceName()],
            $values
        );
    }

    public static function baseDatabaseType(): string
    {
        return JsonType::class;
    }

    public function type(): string
    {
        return $this->getBlock()->serviceName();
    }

    public function jsonSerialize()
    {
        return \array_merge(
            ['_type' => $this->getBlock()->serviceName()],
            $this->value()
        );
    }

    public static function serviceName(): string
    {
        return 'block';
    }

    /**
     * @return string|void
     */
    public function serialize()
    {
        return \serialize([
            'value' => $this->value,
            'blockType' => $this->blockType,
        ]);
    }

    public function withTemplate(string $template): BlockType
    {
        $block = clone $this;
        $block->template = $template;

        return $block;
    }

    public function unserialize($serialized)
    {
        /** @var BlockType $type */
        $type = Type::get(BlockType::serviceName());
        $this->builder = $type->builder;
        $this->renderer = $type->renderer;
        $this->applicationConfig = $type->applicationConfig;
        $this->blockSubManager = $type->blockSubManager;

        $unserialized = \unserialize($serialized);
        $this->value = $unserialized['value'];
        $this->blockType = $unserialized['blockType'];
    }
}
