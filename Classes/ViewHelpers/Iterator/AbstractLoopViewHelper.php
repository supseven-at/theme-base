<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Iterator;

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
     * @return string
     */
    protected function renderIteration(
        int $i,
        int $from,
        int $to,
        int $step,
        string $iterationArgument,
    ): string {
        if (false === empty($iterationArgument)) {
            $variableProvider = $this->renderingContext->getVariableProvider();
            $cycle = (int)(($i - $from) / $step) + 1;
            $iteration = [
                'index'   => $i,
                'cycle'   => $cycle,
                'isOdd'   => !(0 === $cycle % 2),
                'isEven'  => 0 === $cycle % 2,
                'isFirst' => $i === $from,
                'isLast'  => $this->isLast($i, $from, $to, $step),
            ];
            $variableProvider->add($iterationArgument, $iteration);
            $content = (string)$this->renderChildren();
            $variableProvider->remove($iterationArgument);
        } else {
            $content = (string)$this->renderChildren();
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
    protected function isLast(int $i, int $from, int $to, int $step): bool
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
