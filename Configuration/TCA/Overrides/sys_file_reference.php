<?php

declare(strict_types=1);

call_user_func(
    function ($extKey, $table): void {
        /**
         * Additional columns
         */
        $additionalColumns = [
            'caption' => [
                'label'     => 'LLL:EXT:theme_base/Resources/Private/Language/locallang_backend.xlf:field.sys_file_reference.caption.label',
                'l10n_mode' => 'mergeIfNotBlank',
                'config'    => [
                    'default'     => '',
                    'eval'        => 'null',
                    'mode'        => 'useOrOverridePlaceholder',
                    'placeholder' => '__row|uid_local|metadata|caption',
                    'size'        => 20,
                    'type'        => 'text',
                ],
            ],
            'copyright' => [
                'label'     => 'LLL:EXT:theme_base/Resources/Private/Language/locallang_backend.xlf:field.sys_file_reference.copyright.label',
                'l10n_mode' => 'mergeIfNotBlank',
                'config'    => [
                    'default'     => '',
                    'eval'        => 'null',
                    'mode'        => 'useOrOverridePlaceholder',
                    'placeholder' => '__row|uid_local|metadata|copyright',
                    'size'        => 20,
                    'type'        => 'input',
                ],
            ],
        ];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $additionalColumns);

        // use fields caption and copyright instead of description
        // Add palette "tx-theme_base-image-nolink"
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            $table,
            'tx-' . $extKey . '-image-nolink',
            'title,alternative,--linebreak--,
            caption,copyright,--linebreak--,crop'
        );

        // Add palette "tx-theme_base-image-nolink-nocaption-nocopyright"
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            $table,
            'tx-' . $extKey . '-image-nolink-nocaption-nocopyright',
            'title,alternative,--linebreak--,
            --linebreak--,crop'
        );

        // Add palette "tx-theme_base-image-nolink-nocaption"
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            $table,
            'tx-' . $extKey . '-image-nolink-nocaption',
            'title,alternative,--linebreak--,copyright,
            --linebreak--,crop'
        );

        // Add palette "tx-theme_base-nolink-nocaption-nocrop"
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            $table,
            'tx-' . $extKey . '-nolink-nocaption-nocrop',
            'title,alternative,--linebreak--,copyright,
            --palette--;;filePalette'
        );

        // Add palette "tx-theme_base-nolink-nocaption-nocopyright-nocrop"
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            $table,
            'tx-' . $extKey . '-nolink-nocaption-nocopyright-nocrop',
            'title,alternative,--linebreak--,
            --palette--;;filePalette'
        );
        // Add palette "tx-theme_base-nocaption-nocopyright-nocrop"
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            $table,
            'tx-' . $extKey . '-nocaption-nocopyright-nocrop',
            'title,alternative,--linebreak--,link,
            --palette--;;filePalette'
        );
        // Add palette "tx-theme_base-videoOverlay"
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
            $table,
            'tx-' . $extKey . '-videoOverlay',
            'title,alternative,--linebreak--,
            caption,--linebreak--,tx_supi_video_cover'
        );
    },
    'theme_base',
    'sys_file_reference'
);
