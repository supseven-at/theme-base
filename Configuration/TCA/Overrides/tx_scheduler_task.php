<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask;

(static function (): void {
    $GLOBALS['TCA']['tx_scheduler_task']['types'][TableGarbageCollectionTask::class]['taskOptions']['tables'] = [
        'sys_history' => [
            'dateField'    => 'tstamp',
            'expirePeriod' => 60,
        ],
        'sys_log' => [
            'dateField'    => 'tstamp',
            'expirePeriod' => 60,
        ],
        'sys_http_report' => [
            'dateField'    => 'changed',
            'expirePeriod' => 60,
        ],
    ];

    if (ExtensionManagementUtility::isLoaded('solr')) {
        $GLOBALS['TCA']['tx_scheduler_task']['types'][TableGarbageCollectionTask::class]['taskOptions']['tables']['tx_solr_statistics'] = [
            'dateField'    => 'tstamp',
            'expirePeriod' => 60,
        ];
        $GLOBALS['TCA']['tx_scheduler_task']['types'][TableGarbageCollectionTask::class]['taskOptions']['tables']['tx_solr_last_searches'] = [
            'dateField'    => 'tstamp',
            'expirePeriod' => 60,
        ];
    }
})();
