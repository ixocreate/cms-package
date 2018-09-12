<?php
namespace KiwiSuite\Cms\Site\Structure;

use Serializable;

final class Structure implements Serializable
{
    /**
     * @var array
     */
    private $structure = [];

    public function __construct(array $structure)
    {
        $this->structure = $structure;
    }

    public function structure(): array
    {
        return $this->structure;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->structure);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->structure = unserialize($serialized);
    }
}