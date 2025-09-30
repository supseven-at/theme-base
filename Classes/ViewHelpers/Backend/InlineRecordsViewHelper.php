<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Backend;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
    public function __construct(
        protected readonly ConnectionPool $connectionPool,
    ) {
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('uid', 'integer', '', true);
        $this->registerArgument('field', 'string', '', true);
        $this->registerArgument('table', 'string', '', false, 'tt_content');
        $this->registerArgument('sortby', 'string', '', false, 'sorting');
        $this->registerArgument('sortorder', 'string', '', false, 'ASC');
    }

    public function render(): iterable
    {
        $localField = $this->arguments['field'];
        $localTable = $this->arguments['table'];
        $localUid = $this->arguments['uid'];
        $foreignTable = $GLOBALS['TCA'][$localTable]['columns'][$localField]['config']['foreign_table'] ?? null;
        $foreignField = $GLOBALS['TCA'][$localTable]['columns'][$localField]['config']['foreign_field'] ?? null;

        // process db query if foreign table could be fetched from TCA
        if (!empty($foreignTable)) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($foreignTable);
            // Build Query
            $queryBuilder
                ->select('*')
                ->from($foreignTable)
                ->where(
                    $queryBuilder->expr()->eq(
                        $foreignField,
                        $queryBuilder->createNamedParameter($localUid)
                    )
                )
                ->orderBy($this->arguments['sortby'], $this->arguments['sortorder']);

            // foreign_sortby
            if (!empty($GLOBALS['TCA'][$localTable]['columns'][$localField]['config']['foreign_sortby'])) {
                $queryBuilder->orderBy($GLOBALS['TCA'][$localTable]['columns'][$localField]['config']['foreign_sortby']);
            }

            return $queryBuilder->executeQuery()->fetchAllAssociative();
        }

        return [];
    }
}
