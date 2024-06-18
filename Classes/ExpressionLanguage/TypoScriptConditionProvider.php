<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;

class TypoScriptConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            FunctionsProvider::class,
        ];
    }
}
