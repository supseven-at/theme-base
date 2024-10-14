<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Format;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class ConcatFieldsViewHelper is responsible for processing a given
 * record based on specified fields, conditions, and sorting options,
 * and then concatenates the results into a single string using a specified glue.
 *
 * @example
 *
 * {theme:format.concatFields(record: data, fields: "tx_yoastseo_score_readability, tx_yoastseo_score_seo, backend_layout, no_search_sub_entries", conditions: "{
 *      'tx_yoastseo_score_seo': 'bad?foo:bar',
 *      'backend_layout': 'pagets__Startsite?startseite:irgendwas',
 *      'no_search_sub_entries': '0?dies:das'
 * }")}
 */
class ConcatFieldsViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('record', 'array', 'the record set to get the fieldnames from', true);
        $this->registerArgument('fields', 'string', 'Comma separated string of Database Fieldnames', true);
        $this->registerArgument('glue', 'string', 'Should the string separated with a specific glue', false, ' ');
        $this->registerArgument('conditions', 'array', 'Some field specific ternary conditions value:ifTrue:ifFalse', false);
        $this->registerArgument('sort', 'bool', 'Sort the classnames', false, true);
    }

    /**
     * Processes the given record based on specified fields, conditions, and sorting options,
     * then concatenates the results into a single string using a specified glue.
     *
     * @return string The processed and concatenated string based on the input arguments.
     */
    public function render(): string
    {
        $rs = $this->arguments['record'] ?? [];
        $fields = GeneralUtility::trimExplode(',', $this->arguments['fields'], true);
        $result = array_intersect_key($rs, array_flip($fields));
        $glue = $this->arguments['glue'];
        $conditions = $this->arguments['conditions'] ?? [];

        // add the values from the specific field conditions, if available
        foreach ($conditions as $key => $condition) {
            if (isset($result[$key]) && str_contains($condition, '?')) {
                [$conditionValue, $resultValues] = explode('?', $condition);
                [$ifTrue, $ifFalse] = explode(':', $resultValues);

                $result[$key] = ($rs[$key] === $conditionValue) ? $ifTrue : $ifFalse;
            }
        }

        // sort the array values alphabetically
        if ($this->arguments['sort']) {
            asort($result);
        }

        // returns a concatenated string based on the finalized result and the given glue
        return implode($glue, $result);
    }
}
