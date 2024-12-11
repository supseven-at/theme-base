<?php

declare(strict_types=1);

unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processors']['DeferredBackendImageProcessor']);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['initializeFormElement'][1713266835]
    = \Supseven\ThemeBase\Hooks\PrefillFormFieldsWithTestValues::class;
