<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Math;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class SimpleMultiplyViewHelper
 *
 * This view helper performs multiplication of two numbers and optionally, rounds the result.
 * used in the imagerender partial.
 *
 * a fluid inline notation is not possible due to string values are not mathematically operated at this time.
 * that means a {'500' * '2.0'} is not working for now.
 *
 * EXAMPLE:
 *
 * {theme:math.simpleMultiply(a: breakpoint.maxWidth, b: pd.min-ratio) -> f:variable(name: 'resultingMaxWidth')}
 */
class SimpleMultiplyViewHelper extends AbstractViewHelper
{
    /** @var bool */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('a', 'string', 'First number', true);
        $this->registerArgument('b', 'string', 'Second number', true);
        $this->registerArgument('round', 'bool', 'Round result', false);
    }

    /**
     * Return result
     *
     * @return float|string
     */
    public function render(): float|string
    {
        try {
            if ($this->arguments['round']) {
                return round($this->multiplication($this->arguments));
            }

            return self::multiplication($this->arguments);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param $arguments
     *
     * @return float
     */
    protected function multiplication($arguments): float
    {
        return (float)$arguments['a'] * (float)$arguments['b'];
    }
}
