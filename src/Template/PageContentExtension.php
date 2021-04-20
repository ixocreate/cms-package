<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Template;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageVersionCacheable;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Schema\Type\SchemaType;
use Ixocreate\Schema\Type\Type;
use Ixocreate\Template\Extension\ExtensionInterface;

final class PageContentExtension implements ExtensionInterface
{

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var PageVersionCacheable
     */
    private $pageVersionCacheable;

    public function __construct(
        CacheManager $cacheManager,
        PageVersionCacheable $pageVersionCacheable
    ) {
        $this->cacheManager = $cacheManager;
        $this->pageVersionCacheable = $pageVersionCacheable;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'pageContent';
    }

    public function __invoke($page)
    {
        $pageVersion = $this->cacheManager->fetch(
            $this->pageVersionCacheable
                ->withPageId($page->id())
        );

        if (!($pageVersion instanceof PageVersion)) {
            return Type::create([], SchemaType::serviceName());
        }

        return $pageVersion->content();
    }
}
