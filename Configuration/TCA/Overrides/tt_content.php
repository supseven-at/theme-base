<?php

declare(strict_types=1);

call_user_func(
    function ($extKey, $table): void {
        $languageFileBePrefix = 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_backend.xlf:';

        /**
         * Additional columns
         */
        $additionalColumns = [
            'tx_theme_base_link' => [
                'label'  => $languageFileBePrefix . 'field.tt_content.tx_theme_base_link.label',
                'config' => [
                    'type'       => 'input',
                    'renderType' => 'inputLink',
                    'size'       => 50,
                    'max'        => 1024,
                    'eval'       => 'trim',
                    'softref'    => 'typolink',
                ],
            ],
            'tx_theme_base_link_label' => [
                'label'  => $languageFileBePrefix . 'field.tt_content.tx_theme_base_link_label.label',
                'config' => [
                    'type'    => 'input',
                    'size'    => 15,
                    'default' => '',
                    'eval'    => 'trim',
                    'max'     => 30,
                ],
            ],
            'tx_theme_base_link_1' => [
                'label'  => $languageFileBePrefix . 'field.tt_content.tx_theme_base_link.label',
                'config' => [
                    'type'       => 'input',
                    'renderType' => 'inputLink',
                    'size'       => 50,
                    'max'        => 1024,
                    'eval'       => 'trim',
                    'softref'    => 'typolink',
                ],
            ],
            'tx_theme_base_link_label_1' => [
                'label'  => $languageFileBePrefix . 'field.tt_content.tx_theme_base_link_label.label',
                'config' => [
                    'type'    => 'input',
                    'size'    => 15,
                    'default' => '',
                    'eval'    => 'trim',
                    'max'     => 30,
                ],
            ],
        ];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $additionalColumns);

        // Add palette "tx-theme_base-link"
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            $table,
            'tx-' . $extKey . '-link',
            'tx_theme_base_link, tx_theme_base_link_label'
        );

        // Add palette "tx-theme-link-1"
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            $table,
            'tx-' . $extKey . '-link-1',
            'tx_theme_base_link_1, tx_theme_base_link_label_1'
        );
    },
    'theme_base',
    'tt_content'
);
