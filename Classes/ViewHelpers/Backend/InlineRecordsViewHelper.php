<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Backend;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class InlineRecordsViewHelper
 *
 * This class is a ViewHelper that retrieves inline records based on given arguments.
 *
 * EXAMPLE
 * <theme:backend.inlineRecords field="tx_theme_timeline_item" uid="{item.record.uid}" sortby="date DESC" />
 */
class InlineRecordsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('uid', 'integer', '', true);
        $this->registerArgument('field', 'string', '', true);
        $this->registerArgument('table', 'string', '', false, 'tt_content');
        $this->registerArgument('sortby', 'string', '', false, 'sorting');
        $this->registerArgument('sortorder', 'string', '', false, 'ASC');
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $localField = $arguments['field'];
        $localTable = $arguments['table'];
        $localUid = $arguments['uid'];
        $foreignTable = $GLOBALS['TCA'][$localTable]['columns'][$localField]['config']['foreign_table'];
        $foreignField = $GLOBALS['TCA'][$localTable]['columns'][$localField]['config']['foreign_field'];

        // process db query if foreign table could be fetched from TCA
        if (!empty($foreignTable)) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($foreignTable);
            // Build Query
            $queryBuilder
                ->select('*')
                ->from($foreignTable)
                ->where(
                    $queryBuilder->expr()->eq(
                        $foreignField,
                        $queryBuilder->createNamedParameter($localUid, \PDO::PARAM_INT)
                    )
                )
                ->orderBy($arguments['sortby'], $arguments['sortorder']);

            // foreign_sortby
            if (!empty($GLOBALS['TCA'][$localTable]['columns'][$localField]['config']['foreign_sortby'])) {
                $queryBuilder->orderBy($GLOBALS['TCA'][$localTable]['columns'][$localField]['config']['foreign_sortby']);
            }

            return $queryBuilder->execute()->fetchAllAssociative();
        }

        return [];
    }
}
