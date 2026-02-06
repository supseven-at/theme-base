<?php

declare(strict_types=1);

(static function (): void {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem('pages', 'module', [
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category',
        'value' => 'category',
        'icon'  => 'apps-pagetree-folder-contains-categories',
    ]);

    $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-category'] =
        'apps-pagetree-folder-contains-categories';
})();
