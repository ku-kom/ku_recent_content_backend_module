<?php

declare(strict_types=1);
/**
 * Parses RSS and Atom feeds
 */

namespace UniversityOfCopenhagen\kuRecentContentBackendModule\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class RssController extends ActionController
{
    /**
     * Initiate the RequestFactory.
     */
    public function __construct(
        protected readonly RequestFactory $requestFactory,
    ) {
    }

    /**
     * Request url and return response to fluid template.
     * @return ResponseInterface
     */
    public function getFeedAction(): ResponseInterface
    {
        $cObjectData = $this->configurationManager->getContentObject()->data;
        $url = $cObjectData['ku_recent_content_backend_module'];
        $itemsPerPage = $cObjectData['ku_rss_items'] ?? 10;
        

        // Check if the url is valid
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            $message = sprintf('Feed URL is not valid "%s". Update your settings.', $url);
            throw new \RuntimeException($message, 1320651278);
        }

        if (!empty($url)) {
            try {
                $response = $this->requestFactory->request($url, 'GET');
                if ($response->getStatusCode() === 200) {
                    // Check if source is RSS or Atom
                    $feedtype = '';
                    if (false !== strpos($response->getHeaderLine('Content-Type'), 'application/atom+xml')) {
                        $feedtype = 'atom';
                    } elseif (false !== strpos($response->getHeaderLine('Content-Type'), 'application/rss+xml')) {
                        $feedtype = 'rss';
                    }
       
                    $data = $response->getBody()->getContents();
                    
                    // Fix namespaces
                    $data = str_replace('<content:encoded>', '<contentEncoded>', $data);
                    $data = str_replace('</content:encoded>', '</contentEncoded>', $data);

                    // Turn xml into json
                    $xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NOCDATA);
                    $json = json_encode($xml);
                    $feed = json_decode($json, true);

                    switch ($feedtype == 'atom') {
                        case 'atom':
                            // Feed is Atom
                            $items = $feed['entry'];
                            if ($items) {
                                $this->view->assign('type', 'atom');
                                $this->view->assign('title', $feed['title']);
                            }
                            break;
                        default:
                            // Feed is RSS or text/xml
                            $items = $feed['channel']['item'];
                            if ($items) {
                                $this->view->assign('type', 'rss');
                                $this->view->assign('title', $feed['channel']['title']);
                                $this->view->assign('description', $feed['channel']['description']);
                            }
                            break;
                    }

                    if ($items) {
                        // $this->view->assign('feed', $items);
                        // Only return the user-defined number of items:
                        $this->view->assign('feed', array_slice($items, 0, $itemsPerPage));
                        $this->view->assign('data', $cObjectData);
                    }
                } else {
                    // Sisplay error message
                    $this->addFlashMessage(
                        $this->getLanguageService()->sL('LLL:EXT:ku_recent_content_backend_module/Resources/Private/Language/locallang.xlf:error_msg'),
                        '',
                        FlashMessage::ERROR,
                        false
                    );
                }
            } catch (\Exception $e) {
                // Display error message
                $this->addFlashMessage(
                    'Error: ' . $e->getMessage(),
                    '',
                    FlashMessage::ERROR,
                    false
                );
            }
        }
        return $this->htmlResponse();
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
