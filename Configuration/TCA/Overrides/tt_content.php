<?php

/*
 * This file is part of the package ku_phonebook.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 * Sep 2022 Nanna Ellegaard, University of Copenhagen.
 */

defined('TYPO3') or die();

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

call_user_func(function () {
    $extensionKey = 'ku_recent_content_backend_module';
    ExtensionUtility::registerPlugin(
        $extensionKey,
        'Pi1',
        'LLL:EXT:ku_recent_content_backend_module/Resources/Private/Language/locallang_be.xlf:title',
        'ku-rss-icon'
    );
});