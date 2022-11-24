<?php

declare(strict_types=1);

namespace UniversityOfCopenhagen\kuRecentContentBackendModule\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class RecentContentController extends ActionController
{
    /**
    * ModuleTemplate object
    *
    * @var ModuleTemplate
    */
    protected $moduleTemplate;
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected PageRepository $pageRepository;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        PageRepository $pageRepository
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->pageRepository = $pageRepository;
    }

    // /**
    //  * @var ModuleTemplate
    //  */
    // protected ModuleTemplate $moduleTemplate;

    // public function initializeAction()
    // {
    //     $moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
    //     $this->moduleTemplate = $moduleTemplateFactory->create($this->request);
    //     $this->generateMenu();
    // }

    public function indexAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'pages' => $this->getRecentPages(100)
        ]);
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        // Adding title, menus, buttons, etc. using $moduleTemplate ...
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    protected function getRecentPagesBatch(int $limit = 1000, int $offset = 0): array
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
            ->orderBy('crdate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->execute()
            ->fetchAll();
        return $result;
    }

    protected function getRecentPages(int $limit): array
    {
        $elements = [];
        $batchLimit = 1000;
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
                    if ($results[$i]['ku_lastpageupdates_timestamp'] === 0) {
                        $results[$i]['ku_lastpageupdates_timestamp'] = $results[$i]['tstamp'];
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
}
