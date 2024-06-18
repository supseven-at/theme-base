<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Backend;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

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
    use CompileWithRenderStatic;

    private const EXCLUDE = [
        PageRepository::DOKTYPE_RECYCLER,
        PageRepository::DOKTYPE_SYSFOLDER,
    ];

    public function initializeArguments(): void
    {
        $this->registerArgument('pageId', 'string', '', true);
        $this->registerArgument('excludedDoktypes', 'string', '', false, implode(',', self::EXCLUDE));
        $this->registerArgument('order', 'string', '', false, 'sorting');
        $this->registerArgument('orderDirection', 'string', '', false, 'ASC');
        $this->registerArgument('limit', 'int', '', false);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $menu = [];
        $excludedDoktypes = explode(',', $arguments['excludedDoktypes']);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');

        $queryBuilder->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->In(
                    'pid',
                    $arguments['pageId'],
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
            ->orderBy($arguments['order'], $arguments['orderDirection']);

        if ($arguments['limit']) {
            $queryBuilder->setMaxResults($arguments['limit']);
        }

        $pages = $queryBuilder
        ->executeQuery()
        ->fetchAllAssociative();

        foreach ($pages as $page) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('pages');

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
