<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Seo\Sitemap;

use DateTimeInterface;

/**
 * Class Url
 * @package Ixocreate\Cms\Seo\Sitemap
 * 
 */
class Url extends \Thepixeldeveloper\Sitemap\Url
{
    /**
     * UrlProxy constructor.
     * @param string $loc
     * @param \DateTimeInterface|null $lastMod
     * @param string|null $priority
     * @param string|null $changeFreq
     */
    public function __construct(string $loc, \DateTimeInterface $lastMod = null, string $priority = null, string $changeFreq = null)
    {
        parent::__construct($loc);

        if ($lastMod !== null) {
            parent::setLastMod($lastMod);
        }
        if ($priority !== null) {
            parent::setPriority($priority);
        }
        if ($changeFreq !== null) {
            parent::setChangeFreq($changeFreq);
        }
    }

    /**
     * @param DateTimeInterface $lastMod
     * @throws \Exception
     */
    public function setLastMod(DateTimeInterface $lastMod)
    {
        throw new \Exception('Immutable, set now allowed');
    }

    /**
     * @param string $changeFreq
     * @throws \Exception
     */
    public function setChangeFreq(string $changeFreq)
    {
        throw new \Exception('Immutable, set now allowed');
    }

    /**
     * @param string $priority
     * @throws \Exception
     */
    public function setPriority(string $priority)
    {
        throw new \Exception('Immutable, set now allowed');
    }
}
