<?php

declare(strict_types=1);

namespace UniversityOfCopenhagen\kuRecentContentBackendModule\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;

final class RecentContentController extends ActionController
{
    public function __construct(
        protected ModuleTemplateFactory $moduleTemplateFactory,
        protected PageRepository $pageRepository,
        protected iconFactory $iconFactory,
        protected PageRenderer $pageRenderer,
    ) {
        $this->pageRenderer = $pageRenderer;
        $this->listItems = (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('ku_recent_content_backend_module', 'itemsPerPage') ?? 100;
    }

    public function indexAction(): ResponseInterface
    {
        $this->view->assign('pages', $this->getRecentPages($this->listItems));
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->pageRenderer->addCssFile('EXT:ku_recent_content_backend_module/Resources/Public/Css/Dist/ku_recent_content_module.min.css');
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $shortCutButton = $buttonBar->makeShortcutButton()->setRouteIdentifier('web_KuRecentContentBackendModuleTxKurecentcontentbackendmodule');
        $buttonBar->addButton($shortCutButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);

        $link = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('web_KuRecentContentBackendModuleTxKurecentcontentbackendmodule');
        $reloadButton = $buttonBar->makeLinkButton()
            ->setHref($link)
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.reload'))
            ->setIcon($this->iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
        $buttonBar->addButton($reloadButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);

        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    protected function getRecentPages(int $limit): array
    {
        $elements = [];
        $batchLimit = $limit;
        $offset = 0;
        do {
            $results = $this->getRecentPagesBatch($batchLimit, $offset);
            for ($i = 0; $i < count($results); $i++) {
                if ($GLOBALS['BE_USER']->doesUserHaveAccess($this->pageRepository->getPage($results[$i]['uid']), 16)) {
                    if ($GLOBALS['BE_USER']->recordEditAccessInternals('pages', $results[$i]['uid'])) {
                        $results[$i]['isEditable'] = 1;
                    }
                    if (time() - $results[$i]['crdate'] <= 60 * 60 * 24 * 2) {
                        $results[$i]['badges']['new'] = 1;
                    }
                    if (time() < $results[$i]['starttime'] && $results[$i]['hidden'] === 0) {
                        $results[$i]['badges']['visibleInFuture'] = 1;
                    }
                    if (time() > $results[$i]['endtime'] && $results[$i]['endtime'] > 0 && $results[$i]['hidden'] === 0) {
                        $results[$i]['badges']['visibleInPast'] = 1;
                    }
                    if (empty($results[$i]['ku_lastpageupdates_timestamp']) || $results[$i]['ku_lastpageupdates_timestamp'] === 0) {
                        $results[$i]['ku_lastpageupdates_timestamp'] = $results[$i]['tstamp'];
                    } else {
                        $results[$i]['ku_lastpageupdates_timestamp'];
                    }
                    $results[$i]['doktypeLabel'] = $this->getDoktypeTranslationString((int)$results[$i]['doktype']);
                    if (substr($results[$i]['doktypeLabel'], 0, 4) === 'LLL:') {
                        $results[$i]['doktypeLabelIsKey'] = true;
                    }

                    if ($results[$i]['cruser_id']) {
                        $results[$i]['ku_creator'] = $this->getAuthorRealName($results[$i]['cruser_id']);
                    }
                
                    if (count($elements) < $limit) {
                        $elements[] = $results[$i];
                    }
                }
            }
            $offset += $batchLimit;
        } while (count($elements) < $limit && count($results) === $batchLimit);
    
        return $elements;
    }
    

    protected function getDoktypeTranslationString(int $key): ?string
    {
        foreach ($GLOBALS['TCA']['pages']['columns']['doktype']['config']['items'] as $item) {
            if ((int)$item[1] === $key) {
                return $item[0];
            }
        }

        return null;
    }

    protected function getAuthorRealName(int $authorUid): ?string
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_users');

        $result = $queryBuilder
        ->select('realName')
        ->from('be_users')
        ->where($queryBuilder->expr()->eq(
            'uid',
            $queryBuilder->createNamedParameter($authorUid, \PDO::PARAM_STR)
        ))
        ->execute() // Change to executeQuery() in TYPO3 v.12
        ->fetch();

        $name = $result['realName'];

        if ($result) {
            return $name;
        }
    }

    protected function getRecentPagesBatch(int $limit = 100, int $offset = 0): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();
        $queryBuilder
            ->getRestrictions()
            ->removeByType(HiddenRestriction::class)
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class);
        $result = $queryBuilder
            ->select('*')
            ->from('pages')
            ->orderBy('ku_lastpageupdates_timestamp', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->execute()
            ->fetchAll();
        return $result;
    }

       /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
