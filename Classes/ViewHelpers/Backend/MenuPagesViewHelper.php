<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Backend;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

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
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('pageIds', 'string', '', true);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $pages = explode(',', $arguments['pageIds']);

        $menu = [];

        foreach ($pages as $page) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('pages');

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
