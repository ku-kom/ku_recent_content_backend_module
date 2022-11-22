<?php

/*
 * This file is part of the package ku_recent_content_backend_module .
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3') or die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Backend\Template\Components\ButtonBar']['getButtonsHook']['MyExt'] = \UniversityOfCopenhagen\KuSaveclose\Hooks\SaveCloseHook::class . '->addSaveCloseButton';