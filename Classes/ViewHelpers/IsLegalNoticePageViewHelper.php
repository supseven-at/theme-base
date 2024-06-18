<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers;

use Supseven\ThemeBase\Service\LegalNoticeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class IsLegalNoticePageViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('pageUid', 'int', 'The current Page UID', true);
        $this->registerArgument('title', 'string', 'The current Page Title', true);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): bool
    {
        /** @var LegalNoticeService $service */
        $service = GeneralUtility::makeInstance(LegalNoticeService::class);

        return $service->isLegalNoticePage((int)$arguments['pageUid'], $arguments['title']);
    }

}
