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
    public function receiveSchema(Builder $builder, array $options = []): SchemaInterface
    {
        /** @var PageTypeInterface $pageType */
        $pageType = $this->get($options['pageType']);

        return $pageType->receiveSchema($builder);
    }

    /**
     * @param string $pageType
     * @param array $usedHandles
     * @return array
     */
    public function allowedChildPageTypes(string $pageType, array $usedHandles): array
    {
        $allowedPageTypes = [];
        /** @var PageTypeInterface $pageType */
        $pageType = $this->get($pageType);

        $allowedChildren = $pageType->allowedChildren();

        if (empty($allowedChildren)) {
            return $allowedPageTypes;
        }

        foreach ($allowedChildren as $childPageType) {
            /** @var PageTypeInterface $childPageType */
            $childPageType = $this->get($childPageType);

            if (empty($childPageType->handle())) {
                $allowedPageTypes[] = $childPageType::serviceName();
                continue;
            }

            if (in_array($childPageType->handle(), $usedHandles)) {
                continue;
            }

            $allowedPageTypes[] = $childPageType::serviceName();
        }

        return $allowedPageTypes;
    }
}
