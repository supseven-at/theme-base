<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Tca
 *
 * this class moves items in a showitem string to a specific position
 *
 * = Example
 *
 * $GLOBALS['TCA']['pages']['types']['4']['showitem'] =
 *      Supseven\Theme\Utility\Tca::moveShowitemItems(
 *          $GLOBALS['TCA']['pages']['types']['4']['showitem'],
 *          '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.shortcut;shortcut',
 *          2
 *      );
 */
class Tca
{
    /**
     * Variables are used to create the defaults in custom cType showitem configuration
     *
     * @var array
     */
    protected static array $showitemDefaults = [
        1 => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
        ',
        2 => '
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
        ',
    ];

    /**
     * Retrieve a common specific showitem part
     *
     * @param int $index
     * @return string
     */
    public static function getShowitemDefault(int $index): string
    {
        if (isset(self::$showitemDefaults[$index])) {
            return self::$showitemDefaults[$index];
        }
        throw new \UnexpectedValueException('showitemDefault with index "' . $index . '" not found', 1521492535);
    }

    /**
     * @param string $showitem
     * @param string $itemToMove
     * @param string $position
     * @return string
     *
     *  EXAMPLE:
     *
     *  Tca::moveShowitemItem($showitem, 'tx_theme_bodytext_1', 'after:tx_theme_related');
     */
    public static function moveShowitemItem(string $showitem, string $itemToMove, string $position): string
    {
        $showitem = GeneralUtility::trimExplode(',', $showitem, true);
        $oldPosition = self::getPosition($showitem, $itemToMove);
        $itemToMove = $showitem[$oldPosition];
        $positionToMoveTo = self::getPosition($showitem, $position);
        array_splice($showitem, $positionToMoveTo, 0, $itemToMove);
        unset($showitem[$oldPosition]);

        return implode(',', $showitem);
    }

    /**
     * adds a showitem item into a specific position
     *
     * @param string $showitem
     * @param string $newItem
     * @param string $position
     * @return string
     *
     * EXAMPLE:
     *
     * Tca::addShowitemItem($showitem, 'tx_theme_related', 'after:tx_theme_bodytext_1');
     */
    public static function addShowitemItem(string $showitem, string $newItem, string $position): string
    {
        $showitem = GeneralUtility::trimExplode(',', $showitem, true);
        $position = self::getPosition($showitem, $position);

        array_splice($showitem, $position, 0, $newItem);

        return implode(',', $showitem);
    }

    /**
     * returns the array key / position of a given showitem item
     *
     * @param array $showitem
     * @param string $position
     * @return int
     */
    private static function getPosition(array $showitem, string $position): int
    {
        $pos = GeneralUtility::trimExplode(':', $position, true);

        if (count($pos) > 1) {
            $findPosition = array_filter($showitem, fn ($v) => str_starts_with($v, $pos[1]));

            return ($pos[0] === 'before') ? key($findPosition) - 1 : key($findPosition) + 1;
        }

        $findPosition = array_filter($showitem, fn ($v) => str_starts_with($v, $position));

        return key($findPosition);
    }
}
