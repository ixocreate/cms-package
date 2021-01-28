<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListAction implements MiddlewareInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    public function __construct(EntityManagerInterface $master, PageTypeSubManager $pageTypeSubManager)
    {
        $this->entityManager = $master;
        $this->pageTypeSubManager = $pageTypeSubManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = $request->getQueryParams()['locale'] ?? '';
        if (empty($locale)) {
            return new ApiErrorResponse('invalid locale');
        }

        $pageType = $request->getQueryParams()['pageType'] ?? null;

        $terminalPageTypes = $this->pageTypeSubManager->getTerminalPageTypes();

        // TODO: filter offline pages
        $sql = 'SELECT p.`id`, `name`, `level`, `pageType` FROM cms_page p LEFT JOIN cms_sitemap s ON (p.sitemapId = s.id) WHERE p.locale = :locale';
        $parameters = [
            'locale' => $locale,
        ];

        if ($pageType !== null) {
            $sql .= ' AND s.pageType = :pageType';
            $parameters['pageType'] = $pageType;
        }
        if (!empty($terminalPageTypes)) {
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('nested_left', 'nested_left', Types::INTEGER);
            $rsm->addScalarResult('nested_right', 'nested_right', Types::INTEGER);
            $query = $this->entityManager->createNativeQuery('SELECT `nestedLeft`, `nestedRight` FROM cms_sitemap s WHERE p.pageType IN (:terminalPageTypes)', $rsm);
            $result = $query->execute(['terminalPageTypes' => $terminalPageTypes]);

            foreach ($result as $row) {
                $sql .= ' AND s.nestedLeft NOT BETWEEN ' . $row['nested_left'] . ' AND ' . $row['nested_right'];
            }
        }
        $term = $request->getQueryParams()['term'] ?? null;
        if (!empty($term)) {
            $sql .= ' AND p.name LIKE :term';
            $parameters['term'] = '%' . $term . '%';
        }

        $sql .= ' ORDER BY nestedLeft';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('level', 'level', Types::INTEGER);
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $result = $query->execute($parameters);

        $items = [];
        $parent = null;
        $parentPath = '';
        $parentPathList = [];
        $lastLevel = 0;
        foreach ($result as $item) {
            if ($lastLevel < $item['level']) {
                $parentPathList[$item['level']] = $parent['name'];
                $lastLevel = $item['level'];

                $parentPath = $path = \implode(' / ', $parentPathList) . ' / ';
            }
            if ($lastLevel > $item['level']) {
                $parentPathList[$item['level']] = $item['name'];
                for ($i = $lastLevel; $i > $item['level']; $i--) {
                    unset($parentPathList[$i]);
                }
                $lastLevel = $item['level'];

                if (!empty($parentPathList)) {
                    $parentPath = $path = \implode(' / ', $parentPathList) . ' / ';
                } else {
                    $parentPath = '';
                }
            }

            $items[] = [
                'id' => $item['id'],
                'name' => $parentPath . $item['name'],
            ];

            $parent = $item;
        }

        return new ApiSuccessResponse($items);
    }
}
