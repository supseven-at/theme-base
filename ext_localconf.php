<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask;

unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processors']['DeferredBackendImageProcessor']);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['initializeFormElement'][1713266835]
    = \Supseven\ThemeBase\Hooks\PrefillFormFieldsWithTestValues::class;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][TableGarbageCollectionTask::class]['options']['tables'] = [
    'sys_history' => [
        'dateField'    => 'tstamp',
        'expirePeriod' => 60,
    ],
    'sys_log' => [
        'dateField'    => 'tstamp',
        'expirePeriod' => 60,
    ],
];

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('solr')) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][TableGarbageCollectionTask::class]['options']['tables']['tx_solr_statistics'] = [
        'dateField'    => 'tstamp',
        'expirePeriod' => 60,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][TableGarbageCollectionTask::class]['options']['tables']['tx_solr_last_searches'] = [
        'dateField'    => 'tstamp',
        'expirePeriod' => 60,
    ];
}

// @todo: remove this for TYPO3 v13
ExtensionManagementUtility::addUserTSConfig(
    '@import "EXT:theme_base/Configuration/user.tsconfig"',
);
