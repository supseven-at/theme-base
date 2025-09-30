<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Math;

use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the result of simple expression
 *
 * Uses the TYPO3 function for simple calculation used
 * in stdWrap.prioriCalc the evaluate the given expression
 *
 * @see https://docs.typo3.org/m/typo3/reference-typoscript/main/en-us/Functions/Stdwrap.html#prioricalc
 *
 * Usage
 * -----
 *
 * <code>
 *     <!-- Tag -->
 *     <f:variable name="num">2</f:variable>
 *     <t:math.calc>2 * {num}</t:math.calc> -> "4"
 *
 *     <!-- Inline -->
 *     <f:variable name="expr">2 * (4 - 2)</f:variable>
 *     {expr -> t:math.calc()} -> "4"
 * </code>
 *
 * EXAMPLE:
 *
 * <f:variable name="test" value="10" />
 * {test * 10} outputs 100
 */
class CalcViewHelper extends AbstractViewHelper
{
    /** @var bool */
    protected $escapeOutput = false;
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('expression', 'string', 'First number');
        $this->registerArgument('round', 'bool', 'Round result', false);
    }

    public function getContentArgumentName(): ?string
    {
        return 'expression';
    }

    /**
     * Return result
     *
     * @return float|string
     */
    public function render(): float|string
    {
        $expression = (string)$this->renderChildren();
        $value = MathUtility::calculateWithParentheses($expression);

        if ($this->arguments['round']) {
            return round((float)$value);
        }

        return $value;
    }
}
