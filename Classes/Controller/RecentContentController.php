<?php

declare(strict_types=1);

namespace UniversityOfCopenhagen\kuRecentContentBackendModule\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;

class RecentContentController extends ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function indexAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'message' => 'Hello World'
        ]);
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        // Adding title, menus, buttons, etc. using $moduleTemplate ...
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }
}
