<?php

/*
 * This file is part of the package ku_recent_content_backend_module .
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3') or die('Access denied.');

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// Module System > Backend Users
$versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
if ($versionInformation->getMajorVersion() < 12) {
    $query = "TYPO3_MODE === 'BE'";
} else {
    $query = ApplicationType::fromRequest($request)->isFrontend();
}

if ($query) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'ku_recent_content_backend_module',
        'web',
        'tx_kurecentcontentbackendmodule',
        'after:web_info',
        [
            \UniversityOfCopenhagen\kuRecentContentBackendModule\Controller\RecentContentController::class => 'index',
        ],
        [
        'access' => 'user,group',
        'iconIdentifier' => 'ku-recent-content-backend-module',
        'labels' => 'LLL:EXT:ku_recent_content_backend_module/Resources/Private/Language/Module/locallang_mod.xlf',
        ]
    );
}
