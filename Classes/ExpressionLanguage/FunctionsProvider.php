<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class FunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            $this->getToInt(),
        ];
    }

    protected function getToInt(): ExpressionFunction
    {
        return new ExpressionFunction(
            'toInt',
            fn ($val)             => sprintf('(!is_int(%1$s) ? (int)%1$s : %1$s)', $val),
            fn ($arguments, $val) => (int)$val
        );
    }
}
