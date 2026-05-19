<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\DataProcessing;

use Doctrine\DBAL\ArrayParameterType;
use Supseven\ThemeBase\Attributes\AsDataProcessor;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Schema\Field\FieldTypeInterface;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Creates a menu starting at the given PID
 *
 * Fetches all data of a menu with a single SQL query, making
 * it much faster and simpler, but does not support all
 * use-cases of TYPO3s MENU cObject
 *
 * Example
 * =======
 *
 * <code>
 *     dataProcessing {
 *         100 = simple-menu
 *         100 {
 *             // All properties can use stdWrap properties
 *
 *             // Load page UIDs which are used as the start of the menu
 *
 *             // Use UID from site settings
 *             // string, required
 *             pid = {$menu.pid}
 *
 *             // Use UIDs in field `pages`
 *             pid.field = pages
 *
 *             // Used UID of first level
 *             pid.data = leveluid : 1
 *
 *             // Level 0 means, include UIDs of property `pid` in the menu
 *             // int, default: 0
 *             minLevel = 0
 *
 *             // Level 1 starts with the pages, that are direct children of the page(s) of property `pid`
 *             minLevel = 1
 *
 *             // Set level to traverse down.
 *             // relative to PIDs, not setting minLevel!
 *             // A value of 2 creates two levels if minLevel is 0, but only one if minLevel is 1
 *             // int, default: 6
 *             maxLevel = 2
 *
 *             // Include pages not in menu, ignoring the setting `nav_hide`
 *             // bool, default: false
 *             includeNotInMenu = 1
 *
 *             // Include "spacer" element, pages with doktype 199
 *             // bool, default: false
 *             includeSpacer = 1
 *
 *             // Double-slash separated list of fields to use for the title of a menu item
 *             // The first non-empty field will be used
 *             // string, default: "nav_title // title"
 *             titleField = nav_title // subtitle // title
 *
 *             // Load sys_file references from the given comma-separated list of fields
 *             // string, default: empty
 *             references = media, og_image
 *
 *             // Name of processed-data entry of the result. This will be the name of the
 *             // variable in the fluid template containing the menu
 *             // string, default: "menu"
 *             as = mainMenu
 *         }
 *     }
 * </code>
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
#[AsDataProcessor('simple-menu')]
class SimpleMenuProcessor implements DataProcessorInterface
{
    public function __construct(
        protected ConnectionPool $connectionPool,
        protected ResourceFactory $resourceFactory,
        protected TcaSchemaFactory $tcaSchemaFactory,
    ) {
    }

    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {
        $pids = GeneralUtility::intExplode(',', (string)$cObj->stdWrapValue('pid', $processorConfiguration), true);

        // Skip if no PIDs found
        if (!$pids) {
            return $processedData;
        }

        $language = $cObj->getRequest()->getAttribute('language');
        $minLevel = (int)$cObj->stdWrapValue('minLevel', $processorConfiguration, 0);
        $maxLevel = (int)$cObj->stdWrapValue('maxLevel', $processorConfiguration, 6);
        $includeSpacer = (bool)$cObj->stdWrapValue('includeSpacer', $processorConfiguration, false);
        $includeNotInMenu = (bool)$cObj->stdWrapValue('includeNotInMenu', $processorConfiguration, false);

        // Fetch flat list of pages data
        $pages = $this->fetchPages($language->getLanguageId(), $minLevel, $maxLevel, $includeSpacer, $includeNotInMenu, ...$pids);

        // Early exit if no pages found
        if (!$pages) {
            return $processedData;
        }

        $referenceFields = GeneralUtility::trimExplode(',', (string)$cObj->stdWrapValue('references', $processorConfiguration));
        $references = [];

        // Only load references if requested
        if ($referenceFields) {
            $references = $this->fetchReferences($referenceFields, $language->getLanguageId(), ...array_column($pages, 'uid'));
        }

        $menu = [];
        $result = [];
        $titleFields = GeneralUtility::trimExplode('//', $cObj->stdWrapValue('titleField', $processorConfiguration, 'nav_title // title'), true);
        $rootLineUids = array_column($cObj->getRequest()->getAttribute('frontend.page.information')->getRootLine(), 'uid');
        $currentPageUid = $cObj->getRequest()->getAttribute('frontend.page.information')->getId();
        $linkPrefix = trim((string)$language->getBase(), '/') . '/';

        // Build nested menu
        foreach ($pages as $data) {
            $title = null;
            $target = $data['target'] ?: '';
            $active = in_array($data['uid'], $rootLineUids);
            $current = $data['uid'] === $currentPageUid;
            $hasSubpages = false;
            $spacer = (int)$data['doktype'] === PageRepository::DOKTYPE_SPACER;

            // @todo: properly build for non-standard pages
            $link = $linkPrefix . trim($data['slug'], '/') . '/';

            foreach ($titleFields as $titleField) {
                if (!empty($data[$titleField])) {
                    $title = $data[$titleField];
                    break;
                }
            }

            $entry = compact('data', 'title', 'link', 'target', 'active', 'current', 'spacer', 'hasSubpages');
            $uid = $data['uid'];
            $pid = $data['pid'];

            if (!empty($references[$uid])) {
                foreach ($references[$uid] as $field => $files) {
                    $entry[$field] = $files;
                }
            }

            $menu[$uid] = $entry;

            if (!empty($menu[$pid])) {
                $menu[$pid]['hasSubpages'] = true;
                $menu[$pid]['children'] ??= [];
                $menu[$pid]['children'][] = &$menu[$uid];
            }

            if ($data['__menu_level'] === $minLevel) {
                $result[] = &$menu[$uid];
            }
        }

        $as = $cObj->stdWrapValue('as', $processorConfiguration, 'menu');

        $processedData[$as] = $result;

        return $processedData;
    }

    protected function fetchPages(int $languageUid, int $minLevel, int $maxLevel, bool $includeSpacer, bool $includeNotInMenu, int ...$pids): array
    {
        $cnx = $this->connectionPool->getConnectionForTable('pages');

        $sqlTmpl = 'WITH RECURSIVE __menu AS
(
    WITH `init` AS (
        %s
    )
    SELECT * FROM `init`
    UNION ALL
    %s
)
%s';
        // The initial query of the CTE is a list of SELECT literals
        $initParts = array_map(
            static fn (int $pid): string => sprintf('SELECT %d AS `uid`, 0 AS `__lvl`', $pid),
            $pids,
        );
        $initQuery = implode(' UNION ', $initParts);

        $qi = static fn ($identifier) => $cnx->quoteIdentifier($identifier);

        // The sub-query of the CTE joins with itself the fetch pages recursively
        $sub = $cnx->createQueryBuilder();
        $sub->select('sub.uid');
        $sub->addSelectLiteral($qi('__lvl') . ' + 1 AS ' . $qi('__lvl'));
        $sub->from('pages', 'sub');
        $sub->innerJoin(
            'sub',
            '__menu',
            'pp',
            $sub->expr()->eq('pp.uid', $qi('sub.pid'))
        );

        // The outer joins with the CTE to fetch the actual data
        $outer = $cnx->createQueryBuilder();

        $tableFields = [];
        $filter = static fn (FieldTypeInterface $field): bool => $field->getName() !== 'uid' && $field->getName() !== 'pid';

        // Fetch only TCA fields
        foreach ($this->tcaSchemaFactory->get('pages')->getFields($filter) as $field) {
            $tableFields[] = 't' . $languageUid . '.' . $field->getName();
        }

        if ($languageUid > 0) {
            $tableFields[] = 't' . $languageUid . '.uid AS _LOCALIZED_UID';
            $tableFields[] = 't' . $languageUid . '.sys_language_uid AS _REQUESTED_OVERLAY_LANGUAGE';
        }

        $outer->select('__menu.__lvl AS __menu_level', 't0.uid', 't0.pid', ...$tableFields);
        $outer->from('__menu');
        $outer->innerJoin('__menu', 'pages', 't0', $outer->expr()->eq('__menu.uid', $qi('t0.uid')));

        // Join to translation if the language is not the default
        if ($languageUid > 0) {
            $outer->innerJoin('t0', 'pages', 't' . $languageUid, (string)$outer->expr()->and(
                $outer->expr()->eq('t0.uid', $qi('t' . $languageUid . '.l10n_parent')),
                $outer->expr()->eq('t' . $languageUid . '.sys_language_uid', $languageUid),
            ));
        }

        $doktypeWhere = $outer->expr()->lt('t' . $languageUid . '.doktype', '100');

        if ($includeSpacer) {
            $doktypeWhere = $outer->expr()->or(
                $outer->expr()->eq('t' . $languageUid . '.doktype', (string)PageRepository::DOKTYPE_SPACER),
                $doktypeWhere,
            );
        }

        $wheres = [$doktypeWhere];

        if (!$includeNotInMenu) {
            $wheres[] = $outer->expr()->eq('t' . $languageUid . '.nav_hide', '0');
        }

        if (!$languageUid) {
            $wheres[] = $outer->expr()->bitAnd('t0.l18n_cfg', 1) . ' = 0';
        }

        $outer->where(
            $outer->expr()->eq('t0.sys_language_uid', '0'),
            $outer->expr()->gte('__menu.__lvl', (string)$minLevel),
            $outer->expr()->lte('__menu.__lvl', (string)$maxLevel),
            ...$wheres,
        );

        // Order by level first, then by sorting
        // This allows us to later build the nested structure in a single loop
        $outer->orderBy('__menu.__lvl');
        $outer->addOrderBy('t0.sorting');

        $sql = sprintf($sqlTmpl, $initQuery, $sub->getSQL(), $outer->getSQL());

        return $cnx->executeQuery($sql)->fetchAllAssociative();
    }

    private function fetchReferences(array $referenceFields, int $languageId, int ...$pids): array
    {
        $references = [];
        $tableFields = [];
        $filter = static fn (FieldTypeInterface $field): bool => $field->getName() !== 'uid' && $field->getName() !== 'pid';
        $fieldPrefix = $languageId ? 't1' : 't0';

        foreach ($this->tcaSchemaFactory->get('sys_file_reference')->getFields($filter) as $field) {
            $tableFields[] = $fieldPrefix . '.' . $field->getName();
        }

        $qb = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');
        $qb->select('t0.uid', 't0.pid', ...$tableFields);
        $qb->from('sys_file_reference', 't0');
        $qb->where(
            $qb->expr()->eq('t0.sys_language_uid', '0'),
            $qb->expr()->eq('t0.tablenames', $qb->quote('pages')),
            $qb->expr()->in('t0.fieldname', $qb->createNamedParameter($referenceFields, ArrayParameterType::STRING)),
            $qb->expr()->in('t0.uid_foreign', $qb->createNamedParameter($pids, ArrayParameterType::INTEGER)),
        );

        if ($languageId) {
            $qb->innerJoin(
                't0',
                'sys_file_reference',
                't1',
                (string)$qb->expr()->and(
                    $qb->expr()->eq('t0.uid', $qb->quoteIdentifier('t1.l10n_parent')),
                    $qb->expr()->eq('t1.sys_language_uid', (string)$languageId),
                ),
            );
        }

        $qb->orderBy('t0.pid');
        $qb->addOrderBy('t0.sorting_foreign');

        $rows = $qb->executeQuery()->fetchAllAssociative();

        foreach ($rows as $row) {
            $page = $row['pid'];
            $field = $row['fieldname'];
            $reference = $this->resourceFactory->getFileReferenceObject($row['uid'], $row, true);

            $references[$page] ??= [];
            $references[$page][$field] ??= [];
            $references[$page][$field][] = $reference;
        }

        return $references;
    }
}
