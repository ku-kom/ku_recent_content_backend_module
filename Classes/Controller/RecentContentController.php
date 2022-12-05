<?php

declare(strict_types=1);

namespace UniversityOfCopenhagen\kuRecentContentBackendModule\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;

final class RecentContentController extends ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected ModuleTemplate $moduleTemplate;
    protected PageRepository $pageRepository;
    protected iconFactory $iconFactory;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->listItems = (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('ku_recent_content_backend_module', 'itemsPerPage') ?? 100;
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $this->iconFactory = GeneralUtility::makeInstance(iconFactory::class);
    }

    public function indexAction(): ResponseInterface
    {
        GeneralUtility::makeInstance(AssetCollector::class)->addStyleSheet($this->request->getControllerExtensionKey(), 'EXT:'. $this->request->getControllerExtensionKey() .'/Resources/Public/Css/Dist/ku_recent_content_module.min.css');

        $this->view->assign('pages', $this->getRecentPages($this->listItems));
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        // Adding title, menus, buttons, etc. using $moduleTemplate ...
        
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        //$uri = (string)$this->uriBuilder->buildUriFromRoute($routeName, '', UriBuilder::SHAREABLE_URL);
        $list = $buttonBar->makeLinkButton()
            ->setHref('#')
            ->setTitle('Button title')
            ->setShowLabelText('Link')
            ->setIcon($moduleTemplate->getIconFactory()->getIcon('actions-document-info', Icon::SIZE_SMALL));
        $buttonBar->addButton($list, ButtonBar::BUTTON_POSITION_RIGHT, 1);

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

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
