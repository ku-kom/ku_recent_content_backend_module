<?php

/*
 * This file is part of the package ku_phonebook.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 * Sep 2022 Nanna Ellegaard, University of Copenhagen.
 */

defined('TYPO3') or die('Access denied.');

call_user_func(function () {
    $extensionKey = 'ku_recent_content_backend_module';

    /**
     * Default TypoScript for ku_recent_content_backend_module
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript',
        'LLL:EXT:ku_recent_content_backend_module/Resources/Private/Language/locallang_be.xlf:title'
    );
});