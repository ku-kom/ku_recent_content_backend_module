<?php
declare(strict_types=1);

/*
 * This file is part of the package ku_recent_content_backend_module.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace UniversityOfCopenhagen\kuRecentContentBackendModule\Backend\Preview;

use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;

class kuRecentContentBackendModulePreviewRenderer implements PreviewRendererInterface
{
    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        // $record = $item->getRecord();
        // return $record['CType'];
        
        return $this->getLanguageService()->sL('LLL:EXT:ku_recent_content_backend_module/Resources/Private/Language/locallang_be.xlf:linklabel');
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $rssUrl = '';
        $record = $item->getRecord();
        if ($record['ku_recent_content_backend_module']) {
            $rssUrl .= $rssUrl;
        }
        return $record['ku_recent_content_backend_module'];
    }

    public function renderPageModulePreviewFooter(GridColumnItem $item): string
    {
        return '';
    }

    public function wrapPageModulePreview(string $previewHeader, string $previewContent, GridColumnItem $item): string
    {
        $previewHeader = $previewHeader ? '<div class="content-element-preview-ctype">' . $previewHeader . '</div>' : '';
        $previewContent = $previewContent ? '<div class="content-element-preview-content">' . $previewContent . '</div>' : '';
        $preview = $previewHeader || $previewContent ? '<div class="ku-recent-content-backend-module">' . $previewHeader . $previewContent . '</div>' : '';
        return $preview;
    }

    /**
     * Returns LanguageService
     *
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService(): \TYPO3\CMS\Core\Localization\LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
