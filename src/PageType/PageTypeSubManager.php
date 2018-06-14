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

use KiwiSuite\Contract\Schema\SchemaInterface;
use KiwiSuite\Contract\Schema\SchemaReceiverInterface;
use KiwiSuite\Schema\Builder;
use KiwiSuite\ServiceManager\SubManager\SubManager;

final class PageTypeSubManager extends SubManager implements SchemaReceiverInterface
{
    public function receiveSchema(Builder $builder, array $options): SchemaInterface
    {
        /** @var PageTypeInterface $pageType */
        $pageType = $this->get($options['pageType']);

        return $pageType->schema($builder);
    }
}
