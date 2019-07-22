<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Essential;

use Ixocreate\Cache\CacheInterface;
use Ixocreate\Cms\Strategy\LoaderInterface;
use Ixocreate\Cms\Strategy\StructureInterface;
use SplFixedArray;

final class Loader implements LoaderInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var SplFixedArray
     */
    private $data;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    private function initialize(): void
    {
        if ($this->initialized === true) {
            return;
        }

        $this->initialized = true;
        $this->data = $this->cache->get(Strategy::CACHE_KEY);
    }

    /**
     * @return string[]
     */
    public function root(): array
    {
        $this->initialize();

        return $this->data[0];
    }

    public function get(string $id): StructureInterface
    {
        $this->initialize();

        //TODO check if exists
        return $this->data[1][$id];
    }
}
