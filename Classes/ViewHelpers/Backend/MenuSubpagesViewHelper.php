<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Backend;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class MenuSubpagesViewHelper
 *
 * Get Subpages from Page with uid=pageId
 * Output: Array
 * = Example =
 *
 * <f:for each="{theme:backend.menuSubpages(pageId: '{item.record.pages}', excludedDoktypes: '199,254,255')}" as="item">
 */
class MenuSubpagesViewHelper extends AbstractViewHelper
{
    private const EXCLUDE = [
        PageRepository::DOKTYPE_SYSFOLDER,
    ];

    public function __construct(
        protected readonly ConnectionPool $connectionPool,
    ) {
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('pageId', 'string', '', true);
        $this->registerArgument('excludedDoktypes', 'string', '', false, implode(',', self::EXCLUDE));
        $this->registerArgument('order', 'string', '', false, 'sorting');
        $this->registerArgument('orderDirection', 'string', '', false, 'ASC');
        $this->registerArgument('limit', 'int', '', false);
    }

    public function render(): iterable
    {
        $menu = [];
        $excludedDoktypes = explode(',', $this->arguments['excludedDoktypes']);

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');

        $queryBuilder->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->In(
                    'pid',
                    $this->arguments['pageId'],
                ),
                $queryBuilder->expr()->eq(
                    'nav_hide',
                    '0',
                ),
                $queryBuilder->expr()->notIn(
                    'doktype',
                    $excludedDoktypes,
                )
            )
            ->orderBy($this->arguments['order'], $this->arguments['orderDirection']);

        if ($this->arguments['limit']) {
            $queryBuilder->setMaxResults($this->arguments['limit']);
        }

        $pages = $queryBuilder
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($pages as $page) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');

            $result = $queryBuilder->select('*')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $page['uid']
                    )
                )
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($result as $row) {
                $menu[] = $row;
            }
        }

        return $menu;
    }
}
