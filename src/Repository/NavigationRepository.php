<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Ixocreate\Cms\Config\Config;
use Ixocreate\Cms\Entity\Navigation;
use Ixocreate\Database\Repository\AbstractRepository;

final class NavigationRepository extends AbstractRepository
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(EntityManagerInterface $master, Config $config)
    {
        parent::__construct($master);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return Navigation::class;
    }

    public function getNavigationForPage(string $id): array
    {
        $navigation = \array_map(function ($value) {
            $value['active'] = false;
            return $value;
        }, $this->config->navigation());
        $navigationResult = $this->findBy(['pageId' => $id]);
        foreach ($navigationResult as $nav) {
            $navigation[$nav->navigation()]['active'] = true;
        }

        return \array_values($navigation);
    }
}
