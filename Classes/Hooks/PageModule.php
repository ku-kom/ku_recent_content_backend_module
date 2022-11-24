<?php
declare(strict_types=1);

namespace UniversityOfCopenhagen\kuRecentContentBackendModule\Hooks;

use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class PageModule
 */
class PageModule
{

    /**
     * @var string
     */
<<<<<<< HEAD
    protected $templatePathAndFile = 'EXT:ku_recent_content_backend_module/Resources/Private/Templates/ResponsiblePageModule.html';
=======
<<<<<<< HEAD
    protected $templatePathAndFile = 'EXT:ku_recent_content_backend_module/Resources/Private/Templates/ResponsiblePageModule.html';
=======
    protected $templatePathAndFile = 'EXT:ku_recent_content_backend_module/Resources/Private/Templates/ListModule.html';
>>>>>>> dev
>>>>>>> 8029fe847e8724118e87396cff393f79ba9052c3

    /**
     * @param array $params
     * @param PageLayoutController $pageLayoutController
     * @return string
     * @throws InvalidExtensionNameException
     */
    public function manipulate(array $params, PageLayoutController $pageLayoutController): string
    {
        unset($params);
        $pageIdentifier = $pageLayoutController->id;
        $properties = $this->getResponsiblePropertiesToPage($pageIdentifier);
        $properties = $properties + $this->getLastChangedPropertiesToPage($pageIdentifier);
        $properties = $properties + $this->getUserPropertiesToIdentifier($properties['userid']);
        return $this->renderMarkup($properties);
    }

    /**
     * @param int $pageIdentifier
     * @return array
     */
    protected function getResponsiblePropertiesToPage(int $pageIdentifier): array
    {
        $properties = $this->getPropertiesToPage($pageIdentifier, ['responsible_name', 'responsible_email']);
        if (empty($properties['responsible_name']) && empty($properties['responsible_email'])) {
            $parentPageIdentifier = $this->getPropertiesToPage($pageIdentifier, ['pid'])['pid'];
            if ($parentPageIdentifier > 0) {
                $properties = $this->getResponsiblePropertiesToPage($parentPageIdentifier);
            }
        }
        return $properties;
    }

    /**
     * @param int $pageIdentifier
     * @param array $properties
     * @return array
     */
    protected function getPropertiesToPage(int $pageIdentifier, array $properties): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();
        $rows = $queryBuilder
            ->select(...$properties)
            ->from('pages')
            ->where('uid=' . (int)$pageIdentifier)
            ->setMaxResults(1)
            ->execute()
            ->fetchAll();
        return $rows[0];
    }

    /**
     * @param int $pageIdentifier
     * @return array
     */
    protected function getLastChangedPropertiesToPage(int $pageIdentifier): array
    {
        $contentElements = $this->getContentElementsToPage($pageIdentifier);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_log')->createQueryBuilder();
        $rows = $queryBuilder
            ->select('userid', 'tstamp')
            ->from('sys_log')
            ->where('tablename="tt_content" and recuid in (' . implode(',', $contentElements) . ')')
            ->orderBy('tstamp', 'desc')
            ->setMaxResults(1)
            ->execute()
            ->fetchAll();
        $properties = [
            'userid' => 0,
            'tstamp' => 0
        ];
        if (!empty($rows[0])) {
            $properties = $rows[0];
        }
        return $properties;
    }

    /**
     * @param int $userIdentifier
     * @return array
     */
    protected function getUserPropertiesToIdentifier(int $userIdentifier): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('be_users')->createQueryBuilder();
        $rows = $queryBuilder
            ->select('username', 'realName', 'email')
            ->from('be_users')
            ->where('uid=' . (int)$userIdentifier)
            ->execute()
            ->fetchAll();
        $properties = [
            'username' => '',
            'realName' => '',
            'email' => ''
        ];
        if (!empty($rows[0])) {
            $properties = $rows[0];
        }
        return $properties;
    }

    /**
     * @param int $pageIdentifier
     * @return array
     */
    protected function getContentElementsToPage(int $pageIdentifier): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content')->createQueryBuilder();
        $rows = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where('pid=' . (int)$pageIdentifier . ' and deleted=0')
            ->execute()
            ->fetchAll();
        $contentElements = [0];
        foreach ($rows as $row) {
            $contentElements[] = $row['uid'];
        }
        return $contentElements;
    }

    /**
     * @param array $properties
     * @return string
     * @throws InvalidExtensionNameException
     */
    protected function renderMarkup(array $properties): string
    {
        /** @var StandaloneView $standaloneView */
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $extensionName = $standaloneView->getRequest()->getControllerExtensionName();
        $standaloneView->getRequest()->setControllerExtensionName($extensionName);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->templatePathAndFile));
        $standaloneView->assignMultiple($properties);
        return $standaloneView->render();
    }
}