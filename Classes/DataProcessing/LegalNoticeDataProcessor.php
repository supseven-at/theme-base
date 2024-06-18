<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\DataProcessing;

use Supseven\Supi\CSP\SupiPolicyExtender;
use Supseven\ThemeBase\Service\LegalNoticeService;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * DataProcessor to show supseven Infos in PageHeader and on Legal Notice Pages in console.log
 *
 * set site.legalNotice.uid in site configuration, to set legal notice uid. otherwise the page title is used
 * to output the information (impressum or legal notice).
 */
class LegalNoticeDataProcessor implements DataProcessorInterface
{
    /** @var PageRenderer $pageRenderer */
    protected PageRenderer $pageRenderer;

    /** @var LegalNoticeService $service */
    protected LegalNoticeService $service;

    public function __construct(PageRenderer $pageRenderer, LegalNoticeService $service)
    {
        $this->pageRenderer = $pageRenderer;
        $this->service = $service;
    }

    /**
     * Process function that adds comment and logo to header comment
     *
     * @param ContentObjectRenderer $cObj The content object renderer
     * @param array $contentObjectConfiguration The content object configuration
     * @param array $processorConfiguration The processor configuration
     * @param array $processedData The processed data
     *
     * @return array The processed data
     * @throws ContentRenderingException|SiteNotFoundException
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        $comment = "\r\n";
        $logo = <<<LOGO
            ╔═╗╦ ╦╔═╗╔═╗╔═╗╦  ╦╔═╗╔╗╔
            ╚═╗║ ║╠═╝╚═╗║╣ ╚╗╔╝║╣ ║║║
            ╚═╝╚═╝╩  ╚═╝╚═╝ ╚╝ ╚═╝╝╚╝
            LOGO;

        $comment .= "\r\n";
        $comment .= LocalizationUtility::translate('LLL:EXT:theme_base/Resources/Private/Language/locallang.xlf:page.headerComment');

        /** @var TypoScriptFrontendController $tsfe */
        $tsfe = $cObj->getRequest()->getAttribute('frontend.controller');
        $tsfe->config['config']['headerComment'] = $comment;

        if ($this->service->isLegalNoticePage($processedData['data']['uid'], $processedData['data']['title'])) {
            $tsfe->config['config']['headerComment'] = $logo . $comment;
            $consoleLog = 'console.log("' . LocalizationUtility::translate('LLL:EXT:theme_base/Resources/Private/Language/locallang.xlf:page.headerComment') . '"); console.log("\\r\\n\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2557   \\u2588\\u2588\\u2557\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557 \\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2557   \\u2588\\u2588\\u2557\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2588\\u2557   \\u2588\\u2588\\u2557\\r\\n\\u2588\\u2588\\u2554\\u2550\\u2550\\u2550\\u2550\\u255D\\u2588\\u2588\\u2551   \\u2588\\u2588\\u2551\\u2588\\u2588\\u2554\\u2550\\u2550\\u2588\\u2588\\u2557\\u2588\\u2588\\u2554\\u2550\\u2550\\u2550\\u2550\\u255D\\u2588\\u2588\\u2554\\u2550\\u2550\\u2550\\u2550\\u255D\\u2588\\u2588\\u2551   \\u2588\\u2588\\u2551\\u2588\\u2588\\u2554\\u2550\\u2550\\u2550\\u2550\\u255D\\u2588\\u2588\\u2588\\u2588\\u2557  \\u2588\\u2588\\u2551\\r\\n\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2551   \\u2588\\u2588\\u2551\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2554\\u255D\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557  \\u2588\\u2588\\u2551   \\u2588\\u2588\\u2551\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557  \\u2588\\u2588\\u2554\\u2588\\u2588\\u2557 \\u2588\\u2588\\u2551\\r\\n\\u255A\\u2550\\u2550\\u2550\\u2550\\u2588\\u2588\\u2551\\u2588\\u2588\\u2551   \\u2588\\u2588\\u2551\\u2588\\u2588\\u2554\\u2550\\u2550\\u2550\\u255D \\u255A\\u2550\\u2550\\u2550\\u2550\\u2588\\u2588\\u2551\\u2588\\u2588\\u2554\\u2550\\u2550\\u255D  \\u255A\\u2588\\u2588\\u2557 \\u2588\\u2588\\u2554\\u255D\\u2588\\u2588\\u2554\\u2550\\u2550\\u255D  \\u2588\\u2588\\u2551\\u255A\\u2588\\u2588\\u2557\\u2588\\u2588\\u2551\\r\\n\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2551\\u255A\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2554\\u255D\\u2588\\u2588\\u2551     \\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2551\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557 \\u255A\\u2588\\u2588\\u2588\\u2588\\u2554\\u255D \\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2551 \\u255A\\u2588\\u2588\\u2588\\u2588\\u2551\\r\\n\\u255A\\u2550\\u2550\\u2550\\u2550\\u2550\\u2550\\u255D \\u255A\\u2550\\u2550\\u2550\\u2550\\u2550\\u255D \\u255A\\u2550\\u255D     \\u255A\\u2550\\u2550\\u2550\\u2550\\u2550\\u2550\\u255D\\u255A\\u2550\\u2550\\u2550\\u2550\\u2550\\u2550\\u255D  \\u255A\\u2550\\u2550\\u2550\\u255D  \\u255A\\u2550\\u2550\\u2550\\u2550\\u2550\\u2550\\u255D\\u255A\\u2550\\u255D  \\u255A\\u2550\\u2550\\u2550\\u255D");';

            // check, if supi is available and use the hash to implement inline js
            // else use a simple nonce value.
            if (ExtensionManagementUtility::isLoaded('supi')) {
                /** @var SupiPolicyExtender $supiPolicyExtender */
                $supiPolicyExtender = GeneralUtility::makeInstance(SupiPolicyExtender::class);
                $supiPolicyExtender->addInlineScript($consoleLog);
                $this->pageRenderer->addHeaderData('<script>' . $consoleLog . '</script>');
            } else {
                $this->pageRenderer->addJsInlineCode('legalNotice', $consoleLog, true, false, true);
            }
        }

        return $processedData;
    }
}
