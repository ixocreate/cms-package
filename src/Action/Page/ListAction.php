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

        if (!empty($terminalPageTypes)) {
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('nestedLeft', 'nestedLeft', Types::INTEGER);
            $rsm->addScalarResult('nestedRight', 'nestedRight', Types::INTEGER);
            $query = $this->entityManager->createNativeQuery('SELECT `nestedLeft`, `nestedRight` FROM cms_sitemap s WHERE s.pageType IN (:terminalPageTypes)', $rsm);
            $result = $query->execute(['terminalPageTypes' => $terminalPageTypes]);

            foreach ($result as $row) {
                $sql .= ' AND s.nestedLeft NOT BETWEEN ' . $row['nestedLeft'] . ' AND ' . $row['nestedRight'];
            }
        }
        $sqlAll = $sql;
        $term = $request->getQueryParams()['term'] ?? null;
        if (!empty($term)) {
            $sql .= ' AND p.name LIKE :term';
            $parameters['term'] = '%' . $term . '%';
        }

        $sqlAll .= ' ORDER BY nestedLeft';
        $sql .= ' ORDER BY nestedLeft';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('pageType', 'pageType');
        $rsm->addScalarResult('level', 'level', Types::INTEGER);
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $result = $query->execute($parameters);
        $resultAll = $this->entityManager->createNativeQuery($sqlAll, $rsm)->execute($parameters);

        $allItems = [];
        $prev = null;
        $parentPath = '';
        $parentPathList = [];
        $lastLevel = 0;
        foreach ($resultAll as $item) {
            if ($lastLevel < $item['level']) {
                $parentPathList[$item['level']] = $prev['name'];
                $lastLevel = $item['level'];

                $parentPath = \implode(' / ', $parentPathList) . ' / ';
            }
            if ($lastLevel > $item['level']) {
                for ($i = $lastLevel; $i > $item['level']; $i--) {
                    unset($parentPathList[$i]);
                }
                $lastLevel = $item['level'];

                if (!empty($parentPathList)) {
                    $parentPath = \implode(' / ', $parentPathList) . ' / ';
                } else {
                    $parentPath = '';
                }
            }

            $prev = $item;

            if ($pageType !== null && $pageType !== $item['pageType']) {
                continue;
            }

            $allItems[$item['id']] = $parentPath . $item['name'];
        }

        $items = [];
        $prev = null;
        foreach ($result as $item) {
            if ($pageType !== null && $pageType !== $item['pageType']) {
                continue;
            }

            $items[] = [
                'id' => $item['id'],
                'name' => $allItems[$item['id']],
            ];
        }

        return new ApiSuccessResponse($items);
    }
}
