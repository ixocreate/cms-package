<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\PageType;

use Ixocreate\Contract\Schema\BuilderInterface;
use Ixocreate\Contract\Schema\SchemaInterface;
use Ixocreate\Contract\Schema\SchemaProviderInterface;
use Ixocreate\ServiceManager\SubManager\SubManager;

final class PageTypeSubManager extends SubManager implements SchemaProviderInterface
{
    /**
     * @param $name
     * @param BuilderInterface $builder
     * @param array $options
     * @return SchemaInterface
     */
    public function provideSchema($name, BuilderInterface $builder, $options = []): SchemaInterface
    {
        /** @var PageTypeInterface $pageType */
        $pageType = $this->get($name);

        return $pageType->provideSchema($name, $builder);
    }

    /**
     * @param string $pageType
     * @param array $usedHandles
     * @return array
     */
    public function allowedChildPageTypes(array $usedHandles, ?string $pageType = null): array
    {
        $allowedPageTypes = [];
        $allowedChildren = [];

        if (!empty($pageType)) {
            /** @var PageTypeInterface $pageType */
            $pageType = $this->get($pageType);
            $allowedChildren = $pageType->allowedChildren();
        } else {
            foreach ($this->getServiceManagerConfig()->getNamedServices() as $checkPageType) {
                /** @var PageTypeInterface $checkPageType */
                $checkPageType = $this->get($checkPageType);

                if ($checkPageType->isRoot() === false) {
                    continue;
                }

                $allowedChildren[] = $checkPageType::serviceName();
            }
        }


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

            if (\in_array($childPageType->handle(), $usedHandles)) {
                continue;
            }

            $allowedPageTypes[] = $childPageType::serviceName();
        }

        return $allowedPageTypes;
    }
}
