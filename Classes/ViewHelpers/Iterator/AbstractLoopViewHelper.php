<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Iterator;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Abstract class with basic functionality for loop view helpers.
 */
abstract class AbstractLoopViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('iteration', 'string', 'Variable name to insert result into, suppresses output');
    }

    /**
     * @param int $i
     * @param int $from
     * @param int $to
     * @param int $step
     * @param string $iterationArgument
     * @param RenderingContextInterface $renderingContext
     * @param \Closure $renderChildrenClosure
     * @return string
     */
    protected static function renderIteration(
        $i,
        $from,
        $to,
        $step,
        $iterationArgument,
        RenderingContextInterface $renderingContext,
        \Closure $renderChildrenClosure
    ) {
        if (false === empty($iterationArgument)) {
            $variableProvider = $renderingContext->getVariableProvider();
            $cycle = (int)(($i - $from) / $step) + 1;
            $iteration = [
                'index'   => $i,
                'cycle'   => $cycle,
                'isOdd'   => 0 === $cycle % 2 ? false : true,
                'isEven'  => 0 === $cycle % 2 ? true : false,
                'isFirst' => $i === $from ? true : false,
                'isLast'  => static::isLast($i, $from, $to, $step),
            ];
            $variableProvider->add($iterationArgument, $iteration);
            $content = $renderChildrenClosure();
            $variableProvider->remove($iterationArgument);
        } else {
            $content = $renderChildrenClosure();
        }

        return $content;
    }

    /**
     * @param int $i
     * @param int $from
     * @param int $to
     * @param int $step
     * @return bool
     */
    protected static function isLast($i, $from, $to, $step)
    {
        if ($from === $to) {
            $isLast = true;
        } elseif ($from < $to) {
            $isLast = ($i + $step > $to);
        } else {
            $isLast = ($i + $step < $to);
        }

        return $isLast;
    }
}
