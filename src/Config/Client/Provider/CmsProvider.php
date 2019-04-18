<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Config\Client\Provider;

use Ixocreate\Admin\Package\Config\AdminConfig;
use Ixocreate\Admin\ClientConfigProviderInterface;
use Ixocreate\Admin\UserInterface;

final class CmsProvider implements ClientConfigProviderInterface
{
    /**
     * @var AdminConfig
     */
    private $adminConfig;

    public function __construct(AdminConfig $adminConfig)
    {
        $this->adminConfig = $adminConfig;
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
        return [
            'preview' => (string) $this->adminConfig->uri() . '/preview',
        ];
    }
}
