<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * A view helper to translate an array of LLL labels using the localization utility.
 *
 * This class iterates over an array of LLL labels provided as an argument
 * and translates each entry using the LocalizationUtility. If a label cannot
 * be translated, the original value is retained.
 *
 * Methods:
 * - initializeArguments: Registers the required arguments for this view helper.
 * - render: Processes the input array, translating each element.
 */
class TranslateArrayViewHelper extends AbstractViewHelper
{
    /**
     * Registers the required argument 'array' with specified constraints and default value.
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('array', 'array', 'the array with LLL labels to translate', true, []);
    }

    /**
     * Translates an array of values using LocalizationUtility. If a translation is not found, the original value is returned.
     *
     * @return array The array with translated values or original values if no translation is available.
     */
    public function render(): array
    {
        return array_map(fn ($value) => LocalizationUtility::translate($value) ?: $value, $this->arguments['array']);
    }
}
