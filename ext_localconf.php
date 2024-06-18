<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

(static function ($extKey): void {
    ExtensionManagementUtility::addUserTSConfig('@import \'EXT:' . $extKey . '/Configuration/user.tsconfig\'');
    ExtensionManagementUtility::addTypoScriptSetup('@import \'EXT:' . $extKey . '/Configuration/TypoScript/root.typoscript\'');

    // Register own RTE (ckeditor) presets
    $rtePresets = [
        'default' => 'Default',
    ];
    foreach ($rtePresets as $identifier => $fileName) {
        $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['theme_' . $identifier]
            = 'EXT:' . $extKey . '/Configuration/RTE/' . $fileName . '.yaml';
    }

    unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processors']['DeferredBackendImageProcessor']);

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['initializeFormElement'][1713266835]
        = \Supseven\ThemeBase\Hooks\PrefillFormFieldsWithTestValues::class;

})('theme_base');
