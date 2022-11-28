<?php

declare(strict_types=1);

namespace UniversityOfCopenhagen\kuRecentContentBackendModule\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Psr\Http\Message\ResponseInterface;

final class RecentContentController extends ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected ModuleTemplate $moduleTemplate;
    protected PageRepository $pageRepository;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
    }

    public function indexAction(): ResponseInterface
    {
        $itemsPerPage = (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('ku_recent_content_backend_module', 'itemsPerPage') ?? 100;
        $this->view->assign('pages', $this->getRecentPages($itemsPerPage));
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        // Adding title, menus, buttons, etc. using $moduleTemplate ...
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    protected function getRecentPages(int $limit): array
    {
        $elements = [];
        $batchLimit = 100;
        $offset = 0;
        do {
            $results = $this->getRecentPagesBatch($batchLimit, $offset);
            for ($i = 0; $i < count($results); $i++) {
                if ($GLOBALS['BE_USER']->doesUserHaveAccess($this->pageRepository->getPage($results[$i]['uid']), 16)) {
                    if (time() - $results[$i]['crdate'] <= 60 * 60 * 24 * 2) {
                        $results[$i]['badges']['new'] = 1;
                    }
                    if (time() < $results[$i]['starttime'] && $results[$i]['hidden'] === 0) {
                        $results[$i]['badges']['visibleInFuture'] = 1;
                    }
                    if (time() > $results[$i]['endtime'] && $results[$i]['endtime'] > 0 && $results[$i]['hidden'] === 0) {
                        $results[$i]['badges']['visibleInPast'] = 1;
                    }
                    if ($results[$i]['ku_lastpageupdates_timestamp'] > 0) {
                        $results[$i]['ku_lastpageupdates_timestamp'];
                    } else {
                        $results[$i]['ku_lastpageupdates_timestamp'] = $results[$i]['tstamp'];
                    }
                    if (count($elements) < $limit) {
                        $elements[] = $results[$i];
                    }
                }
                $elements[] = $results[$i];
            }
            $offset += $batchLimit;
        } while (count($elements) < $limit && count($results) === $batchLimit);
        debug($elements);
        return $elements;
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

}
