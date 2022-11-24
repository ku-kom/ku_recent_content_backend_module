<?php

/*
 * This file is part of the package ku_recent_content_backend_module .
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3') or die('Access denied.');

// Module System > Backend Users
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'ku_recent_content_backend_module',
    'web',
    'ku_recent_content_backend_module',
    'after:web_info',
    [
        \UniversityOfCopenhagen\kuRecentContentBackendModule\Controller\RecentContentController::class => 'index',
    ],
    [
    'access' => 'user, group',
    'icon' => 'EXT:ku_recent_content_backend_module/Resources/Public/Icons/Extension.svg',
    'labels' => 'LLL:EXT:ku_recent_content_backend_module/Resources/Private/Language/Module/locallang_mod.xlf',
    ]
);
