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

namespace KiwiSuite\Cms\PageType;

use KiwiSuite\Contract\Application\SerializableServiceInterface;

final class PageTypeMapping implements SerializableServiceInterface
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * PageTypeMapping constructor.
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @return array
     */
    public function getMapping() : array
    {
        return $this->mapping;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return \serialize($this->mapping);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->mapping = \unserialize($serialized);
    }
}