<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (ExtensionManagementUtility::isLoaded('form')) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['initializeFormElement'][1713266835]
        = \Supseven\ThemeBase\Hooks\PrefillFormFieldsWithTestValues::class;

    ExtensionManagementUtility::addTypoScriptSetup('@import \'EXT:theme_base/Configuration/TypoScript/root.typoscript\'');
}

if (ExtensionManagementUtility::isLoaded('solr')) {
    unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processors']['DeferredBackendImageProcessor']);
}

// Set default scheme for all external links added through the link wizard
$GLOBALS['TYPO3_CONF_VARS']['SYS']['defaultScheme'] = 'https';
