<?php

/*
 * This file is part of the package ku_recent_content_backend_module.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 * Sep 2022 Nanna Ellegaard, University of Copenhagen.
 */

defined('TYPO3') or die('Access denied.');

$contentTypeName = 'ku_recent_content_backend_module';
$ll = 'LLL:EXT:'.$contentTypeName.'/Resources/Private/Language/locallang_be.xlf:';

// Add Content Element
if (!is_array($GLOBALS['TCA']['tt_content']['types'][$contentTypeName] ?? false)) {
    $GLOBALS['TCA']['tt_content']['types'][$contentTypeName] = [];
}

// Add content element to selector list
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        $ll . 'title',
        $contentTypeName,
        'ku-rss-icon'
    ],
    'special',
    'after'
);

// KU RSS/Atom content element
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', [
    'ku_recent_content_backend_module' => [
        'exclude' => 0,
        'label' => $ll . 'linklabel',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputLink',
            // Replace with the following in v.12:
            //'type' => 'link',
            //'allowedTypes' => ['page', 'url', 'record'],
            'eval' => 'lower,required'
        ],
    ],
    'ku_rss_items' => [
        'exclude' => 0,
        'label' => $ll . 'items',
        'config' => [
           'type' => 'text',
           'renderType' => 'input',
           'size' => 5,
           'eval' => 'trim,int',
           'min' => 1,
           'max' => 100,
           'default' => 10,
        ],
     ],
     'ku_rss_layout' => [
        'exclude' => 0,
        'label' => $ll . 'layout',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [$ll . 'layuot_tiles', 'layout_tiles', 'EXT:'.$contentTypeName.'/Resources/Public/Icons/layout_tiles.svg'],
                [$ll . 'layuot_list', 'layout_list', 'EXT:'.$contentTypeName.'/Resources/Public/Icons/layout_list.svg'],
            ],
            'fieldWizard' => [
                'selectIcons' => [
                    'disabled' => false,
                ],
            ],
            'default' => 'layout_tiles',

        ],
    ],
]);

$ku_recent_content_backend_module_settings = [
    'showitem' => '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.headers;headers,ku_recent_content_backend_module,ku_rss_items,ku_rss_layout,
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
        --palette--;;language,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
        --palette--;;hidden,
        --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
        rowDescription,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
    ',
];

// Configure element type
$GLOBALS['TCA']['tt_content']['types'][$contentTypeName] = $ku_recent_content_backend_module_settings;

/**
 * Registers backend previewRenderer
 */

$GLOBALS['TCA']['tt_content']['types'][$contentTypeName]['previewRenderer'] = \UniversityOfCopenhagen\kuRecentContentBackendModule\Backend\Preview\kuRecentContentBackendModulePreviewRenderer::class;
