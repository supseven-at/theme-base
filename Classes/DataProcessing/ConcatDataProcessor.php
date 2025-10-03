<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\DataProcessing;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Class ConcatDataProcessor
 *
 * This class is responsible for processing data by concatenating values based
 * on a given configuration. It implements the DataProcessorInterface.
 *
 * can be used on every recordset.
 *
 * @example
 *
 * 1728909618 = Supseven\ThemeBase\DataProcessing\ConcatDataProcessor
 * 1728909618 {
 *      fields {
 *          tx_yoastseo_score_readability = bad ? readability_true : readability_false
 *          tx_yoastseo_score_seo =
 *          backend_layout = pagets__Startsite ? layout_true : layout_false
 *          no_search_sub_entries = 0 ? entries_true : entries_false
 *      }
 *
 *      sort = 0
 *      as = myClassNames
 * }
 *
 * fields = record set fields.
 * key = fieldname of the record set to filter
 * value = ternary condition = `expected_value ? value_if_true : value_if_false` - you can use `expected_value ?: value_if_false also`- if no value, the value of the recordset is returned
 * sort = sort classnames alphabetically
 * as = the returned variable name usable in fluid
 */
#[AutoconfigureTag('data.processor', ['identifier' => 'concat'])]
class ConcatDataProcessor implements DataProcessorInterface
{
    /**
     * Processes the given data and returns the modified processed data array.
     *
     * @param ContentObjectRenderer $cObj The content object renderer for stdWrap values.
     * @param array $contentObjectConfiguration The configuration array for content objects.
     * @param array $processorConfiguration The configuration array for the processor.
     * @param array $processedData The data array that has been processed so far.
     * @return array Returns the modified processed data array after applying the processor logic.
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {
        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration) ?: 'cssClassNames';
        $glue = $cObj->stdWrapValue('glue', $processorConfiguration, ' ');
        $sort = (bool)$cObj->stdWrapValue('sort', $processorConfiguration, true);

        $data = $processedData['data'] ?: [];
        $filter = $processorConfiguration['fields.'] ?? [];
        $result = array_intersect_key($data, $filter);

        foreach ($result as $key => $value) {
            if (isset($filter[$key])) {
                $condition = $filter[$key];

                if ($condition) {
                    $result[$key] = $this->evaluateCondition($condition, $value);
                }
            }
        }

        if ($sort) {
            asort($result);
        }

        $processedData[$targetVariableName] = implode($glue, $result);

        return $processedData;
    }

    /**
     * Evaluates a given condition and returns a corresponding string based on the value.
     *
     * @param string $condition The condition string that contains the criteria for evaluation.
     * @param int|string $value The value to be checked against the condition.
     * @return string Returns the corresponding string based on whether the condition is met or not.
     */
    private function evaluateCondition(string $condition, int|string $value): string
    {
        $parts = GeneralUtility::trimExplode('?', $condition);
        $conditionCheck = is_int($value) ? (int)$parts[0] : $parts[0];
        [$isTrue, $isFalse] = GeneralUtility::trimExplode(':', $parts[1]);

        return $value === $conditionCheck ? $isTrue : $isFalse;
    }
}
