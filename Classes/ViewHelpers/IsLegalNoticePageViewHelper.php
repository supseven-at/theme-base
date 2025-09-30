<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers;

use Supseven\ThemeBase\Service\LegalNoticeService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class IsLegalNoticePageViewHelper extends AbstractViewHelper
{
    public function __construct(
        protected readonly LegalNoticeService $legalNoticeService,
    ) {
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('pageUid', 'int', 'The current Page UID', true);
        $this->registerArgument('title', 'string', 'The current Page Title', true);
    }

    public function render(): bool
    {
        $pageUid = (int)$this->arguments['pageUid'];
        $title = (string)$this->arguments['title'];

        return $this->legalNoticeService->isLegalNoticePage($pageUid, $title);
    }

}
