<?php
declare(strict_types=1);

namespace KiwiSuite\Cms\Config\Client\Provider;

use KiwiSuite\Admin\Config\AdminConfig;
use KiwiSuite\Contract\Admin\ClientConfigProviderInterface;
use KiwiSuite\Contract\Admin\RoleInterface;

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

    /**
     * @param RoleInterface|null $role
     * @return array
     */
    public function clientConfig(?RoleInterface $role = null): array
    {
        if (empty($role)) {
            return [];
        }
        return [
            'preview' => (string) $this->adminConfig->uri() . '/preview',
        ];
    }

    public static function serviceName(): string
    {
        return 'cms';
    }
}
