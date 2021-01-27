<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\PageType;

use Ixocreate\Schema\Builder\BuilderInterface;
use Ixocreate\Schema\SchemaInterface;
use Ixocreate\Schema\SchemaProviderInterface;
use Ixocreate\ServiceManager\SubManager\AbstractSubManager;

final class PageTypeSubManager extends AbstractSubManager implements SchemaProviderInterface
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
    public function allowedPageTypes(array $usedHandles, ?string $pageType = null): array
    {
        $allowedChildren = [];

        $namedServices = $this->getServiceManagerConfig()->getNamedServices();

        if (!empty($pageType)) {
            /** @var PageTypeInterface $pageType */
            $pageType = $this->get($pageType);
            $allowedChildren = $pageType->allowedChildren();
        } else {
            foreach ($namedServices as $serviceName => $className) {
                if (!\is_subclass_of($className, RootPageTypeInterface::class)) {
                    continue;
                }

                $allowedChildren[] = $className::serviceName();
            }
        }

        if (empty($allowedChildren)) {
            return [];
        }

        $allowedPageTypes = [];


        foreach ($allowedChildren as $childPageType) {
            $className = $namedServices[$childPageType];

            if (\is_subclass_of($className, HandlePageTypeInterface::class) &&
                \in_array($className::serviceName(), $usedHandles)) {
                continue;
            }

            $allowedPageTypes[] = $className::serviceName();
        }

        return $allowedPageTypes;
    }
}
