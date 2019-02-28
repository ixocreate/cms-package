<?php
/**
 * Created by PhpStorm.
 * User: jjost
 * Date: 2019-02-26
 * Time: 13:02
 */
declare(strict_types=1);

namespace Ixocreate\Cms\Seo\Sitemap;

use Thepixeldeveloper\Sitemap\Urlset;

final class UrlsetCollector
{
    const LIMIT = 50000;

    /**
     * @var Urlset[]
     */
    private $collections;

    /**
     * @var int
     */
    private $count;

    /**
     * SitemapSplitter constructor.
     */
    public function __construct()
    {
        $this->collections = [];
        $this->count = 0;
    }

    public function add(Url $url)
    {
        if ($this->count === 0 || $this->count === self::LIMIT) {
            $this->count = 0; $this->collections[] = new Urlset();
        }

        $this->collections[count($this->collections) - 1]->add($url);
        $this->count++;
    }

    /**
     * @return Collection[]
     */
    public function getCollections(): array
    {
        return $this->collections;
    }
}