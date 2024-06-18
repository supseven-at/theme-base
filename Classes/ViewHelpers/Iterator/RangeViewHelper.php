<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Iterator;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class RangeViewHelper
 *
 * This ViewHelper generates an array with a range of numbers, given a starting number and an end number and returns
 * an resulting array
 *
 * EXAMPLE:
 *
 * <theme:iterator.range to="10" from="0" />
 */
class RangeViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('from', 'integer', 'Starting number for the range', false, 0);
        $this->registerArgument('to', 'integer', 'End number for the range', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): array {
        $to = (int)$arguments['to'];
        $from = (int)$arguments['from'];

        return range($from, $to);
    }
}
