
<?php

defined('TYPO3') or die('Access denied.');
/*
 * This file is part of the package ku_recent_content_backend_module.
 * */

use UniversityOfCopenhagen\kuRecentContentBackendModule\Controller\RecentContentController;

/**
 * Definitions for modules
 */
return [
    'web_examples' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'workspaces' => 'live',
        'labels' => 'LLL:EXT:ku_recent_content_backend_module/Resources/Private/Language/Module/locallang_mod.xlf',
        'iconIdentifier' => 'ku-recent-content-backend-module',
        'extensionName' => 'kuRecentContentBackendModule',
        'controllerActions' => [
            RecentContentController::class => [
                'index, flash','tree','clipboard','links','fileReference','fileReferenceCreate',
            ],
        ],
    ]
];