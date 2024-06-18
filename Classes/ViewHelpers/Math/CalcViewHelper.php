<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Math;

use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

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
 * @deprecated
 * you might use fluid inline notation if no rounding is needed
 *
 * EXAMPLE:
 *
 * <f:variable name="test" value="10" />
 * {test * 10} outputs 100
 */
class CalcViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /** @var bool */
    protected $escapeOutput = false;
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('expression', 'string', 'First number');
        $this->registerArgument('round', 'bool', 'Round result', false);
    }

    /**
     * Return result
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return float|string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): float|string
    {
        $expression = (string)$renderChildrenClosure();

        $value = MathUtility::calculateWithParentheses($expression);

        if ($arguments['round']) {
            return round((float)$value);
        }

        return $value;
    }
}
