<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Backend;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class MenuPagesViewHelper
 *
 * Output: Array
 * = Example =
 *
 * <f:for each="{theme:backend.menuPages(pageIds: '{pages}')}" as="item">
 */
class MenuPagesViewHelper extends AbstractViewHelper
{
    public function __construct(
        protected readonly ConnectionPool $connectionPool,
    ) {
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('pageIds', 'string', '', true);
    }

    public function render(): iterable
    {
        $pages = explode(',', $this->arguments['pageIds']);

        $menu = [];

        foreach ($pages as $page) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');

            $result = $queryBuilder->select('uid', 'title', 'doktype')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $page
                    )
                )
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($result as $entry) {
                $menu[] = $entry;
            }
        }

        return $menu;
    }
}
