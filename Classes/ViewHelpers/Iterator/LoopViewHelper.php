<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Iterator;

/**
 * Repeats rendering of children $count times while updating $iteration.
 *
 * mostly used in styling phases, when not enough data is available.
 *
 * EXAMPLE:
 * <theme:iterator.loop count="10">
 * <p>test</p>
 * </theme:iterator.loop>
 */
class LoopViewHelper extends AbstractLoopViewHelper
{
    /**
     * Initialize
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('count', 'integer', 'Number of times to render child content', true);
        $this->registerArgument('minimum', 'integer', 'Minimum number of loops before stopping', false, 0);
        $this->registerArgument('maximum', 'integer', 'Maxiumum number of loops before stopping', false, PHP_INT_MAX);
    }

    public function render()
    {
        $count = (int)$this->arguments['count'];
        $minimum = (int)$this->arguments['minimum'];
        $maximum = (int)$this->arguments['maximum'];
        $iteration = $this->arguments['iteration'];
        $content = '';
        $variableProvider = $this->renderingContext->getVariableProvider();

        if ($count < $minimum) {
            $count = $minimum;
        } elseif ($count > $maximum) {
            $count = $maximum;
        }

        if (true === $variableProvider->exists($iteration)) {
            $backupVariable = $variableProvider->get($iteration);
            $variableProvider->remove($iteration);
        }

        for ($i = 0; $i < $count; $i++) {
            $content .= $this->renderIteration(
                $i,
                0,
                $count,
                1,
                $iteration
            );
        }

        if (true === isset($backupVariable)) {
            $variableProvider->add($iteration, $backupVariable);
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
    protected function isLast($i, $from, $to, $step): bool
    {
        return $i + $step >= $to;
    }
}
