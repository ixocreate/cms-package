<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Config\Client\Provider;

use Ixocreate\Admin\ClientConfigProviderInterface;
use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Admin\UserInterface;
use Ixocreate\Schema\Link\LinkInterface;
use Ixocreate\Schema\Link\LinkListInterface;
use Ixocreate\Schema\Link\LinkManager;

final class CmsProvider implements ClientConfigProviderInterface
{
    /**
     * @var AdminConfig
     */
    private $adminConfig;

    /**
     * @var LinkManager
     */
    private $linkManager;

    public function __construct(
        AdminConfig $adminConfig,
        LinkManager $linkManager
    ) {
        $this->adminConfig = $adminConfig;
        $this->linkManager = $linkManager;
    }

    public static function serviceName(): string
    {
        return 'cms';
    }

    /**
     * @param UserInterface|null $user
     * @return array
     */
    public function clientConfig(?UserInterface $user = null): array
    {
        if (empty($user)) {
            return [];
        }

        $linkTypes = [];
        foreach ($this->linkManager->services() as $serviceName) {
            /** @var LinkInterface $linkType */
            $linkTypeService = $this->linkManager->get($serviceName);
            $linkType = [
                'type' => $linkTypeService->serviceName(),
                'label' => $linkTypeService->label(),
                'hasLocales' => null,
                'listUrl' => null,
            ];
            if ($linkTypeService instanceof LinkListInterface) {
                $linkType = \array_merge($linkType, [
                    'hasLocales' => $linkTypeService->hasLocales(),
                    'listUrl' => $linkTypeService->listUrl(),
                ]);
            }
            $linkTypes[] = $linkType;
        }

        return [
            'linkTypes' => $linkTypes,
            'preview' => (string)$this->adminConfig->uri() . '/preview',
        ];
    }
}
