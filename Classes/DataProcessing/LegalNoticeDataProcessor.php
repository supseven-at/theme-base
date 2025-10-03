<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\DataProcessing;

use Supseven\ThemeBase\Service\LegalNoticeService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * DataProcessor to show supseven Infos in PageHeader and on Legal Notice Pages in console.log
 *
 * set site.legalNotice.uid in site configuration, to set legal notice uid. otherwise the page title is used
 * to output the information (impressum or legal notice).
 */
#[AutoconfigureTag('data.processor', ['identifier' => 'legal-notice'])]
class LegalNoticeDataProcessor implements DataProcessorInterface
{
    public function __construct(
        protected readonly PageRenderer $pageRenderer,
        protected readonly LegalNoticeService $service,
        #[Autowire(service: 'typo3.lang')]
        protected readonly LanguageService $languageService,
    ) {
    }

    /**
     * Process function that adds comment and logo to header comment
     *
     * @param ContentObjectRenderer $cObj The content object renderer
     * @param array $contentObjectConfiguration The content object configuration
     * @param array $processorConfiguration The processor configuration
     * @param array $processedData The processed data
     * @return array The processed data
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        $label = $this->languageService->sL('LLL:EXT:theme_base/Resources/Private/Language/locallang.xlf:page.headerComment');
        $comment = "\r\n";
        $logo = <<<LOGO
            ╔═╗╦ ╦╔═╗╔═╗╔═╗╦  ╦╔═╗╔╗╔
            ╚═╗║ ║╠═╝╚═╗║╣ ╚╗╔╝║╣ ║║║
            ╚═╝╚═╝╩  ╚═╝╚═╝ ╚╝ ╚═╝╝╚╝
            LOGO;

        $comment .= "\r\n";
        $comment .= $label;

        $typoscript = $cObj->getRequest()->getAttribute('frontend.typoscript');
        $config = $typoscript->getConfigArray();
        $setup = $typoscript->getSetupArray();

        if ($this->service->isLegalNoticePage($processedData['data']['uid'], $processedData['data']['title'])) {
            $consoleLog = 'console.log(' . json_encode($comment) . '); console.log("\\r\\n\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2557   \\u2588\\u2588\\u2557\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557 \\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2557   \\u2588\\u2588\\u2557\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2588\\u2557   \\u2588\\u2588\\u2557\\r\\n\\u2588\\u2588\\u2554\\u2550\\u2550\\u2550\\u2550\\u255D\\u2588\\u2588\\u2551   \\u2588\\u2588\\u2551\\u2588\\u2588\\u2554\\u2550\\u2550\\u2588\\u2588\\u2557\\u2588\\u2588\\u2554\\u2550\\u2550\\u2550\\u2550\\u255D\\u2588\\u2588\\u2554\\u2550\\u2550\\u2550\\u2550\\u255D\\u2588\\u2588\\u2551   \\u2588\\u2588\\u2551\\u2588\\u2588\\u2554\\u2550\\u2550\\u2550\\u2550\\u255D\\u2588\\u2588\\u2588\\u2588\\u2557  \\u2588\\u2588\\u2551\\r\\n\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2551   \\u2588\\u2588\\u2551\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2554\\u255D\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557  \\u2588\\u2588\\u2551   \\u2588\\u2588\\u2551\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557  \\u2588\\u2588\\u2554\\u2588\\u2588\\u2557 \\u2588\\u2588\\u2551\\r\\n\\u255A\\u2550\\u2550\\u2550\\u2550\\u2588\\u2588\\u2551\\u2588\\u2588\\u2551   \\u2588\\u2588\\u2551\\u2588\\u2588\\u2554\\u2550\\u2550\\u2550\\u255D \\u255A\\u2550\\u2550\\u2550\\u2550\\u2588\\u2588\\u2551\\u2588\\u2588\\u2554\\u2550\\u2550\\u255D  \\u255A\\u2588\\u2588\\u2557 \\u2588\\u2588\\u2554\\u255D\\u2588\\u2588\\u2554\\u2550\\u2550\\u255D  \\u2588\\u2588\\u2551\\u255A\\u2588\\u2588\\u2557\\u2588\\u2588\\u2551\\r\\n\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2551\\u255A\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2554\\u255D\\u2588\\u2588\\u2551     \\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2551\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557 \\u255A\\u2588\\u2588\\u2588\\u2588\\u2554\\u255D \\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2588\\u2557\\u2588\\u2588\\u2551 \\u255A\\u2588\\u2588\\u2588\\u2588\\u2551\\r\\n\\u255A\\u2550\\u2550\\u2550\\u2550\\u2550\\u2550\\u255D \\u255A\\u2550\\u2550\\u2550\\u2550\\u2550\\u255D \\u255A\\u2550\\u255D     \\u255A\\u2550\\u2550\\u2550\\u2550\\u2550\\u2550\\u255D\\u255A\\u2550\\u2550\\u2550\\u2550\\u2550\\u2550\\u255D  \\u255A\\u2550\\u2550\\u2550\\u255D  \\u255A\\u2550\\u2550\\u2550\\u2550\\u2550\\u2550\\u255D\\u255A\\u2550\\u255D  \\u255A\\u2550\\u2550\\u2550\\u255D");';
            $this->pageRenderer->addJsInlineCode('legalNotice', $consoleLog, true, false, true);
            $comment = $logo . $comment;
        }

        $config['headerComment'] = $setup['config.']['headerComment'] = $comment;

        $typoscript->setConfigArray($config);
        $typoscript->setSetupArray($setup);

        return $processedData;
    }
}
