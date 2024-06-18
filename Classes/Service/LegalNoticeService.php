<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;

class LegalNoticeService
{
    /** @var SiteFinder $siteFinder */
    protected SiteFinder $siteFinder;

    public function __construct(SiteFinder $siteFinder)
    {
        $this->siteFinder = $siteFinder;
    }

    /**
     * Checks if a page is a legal notice page.
     *
     * @param int $pageUid The UID of the page.
     * @param string $title The title of the page.
     * @return bool Returns true if the page is a legal notice page, otherwise false.
     * @throws SiteNotFoundException
     */
    public function isLegalNoticePage(int $pageUid, string $title): bool
    {
        $siteConfig = $this->siteFinder->getSiteByPageId($pageUid)->getConfiguration()['settings'] ?? null;
        // @todo: refactor site setting key "site" to "themeBase"
        $legalNoticeUid = $siteConfig['site']['legalNotice']['uid'] ?? 0;

        if (Environment::getContext()->isDevelopment()) {
            return false;
        }

        if ($this->isPageTitleLegalNotice($title)) {
            return true;
        }

        if ($this->isPageIdLegalNoticeUid($pageUid, $legalNoticeUid)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the page title indicates a legal notice page.
     *
     * @param string $title The title of the page.
     * @return bool Returns true if the page title indicates a legal notice page, otherwise false.
     */
    private function isPageTitleLegalNotice(string $title): bool
    {
        return stripos($title, 'impressum') !== false || stripos($title, 'legal notice') !== false;
    }

    /**
     * Checks if the given page UID is equal to the legal notice UID.
     *
     * @param int $pageUid The UID of the page.
     * @param int $legalNoticeUid The legal notice UID.
     * @return bool Returns true if the page UID is equal to the legal notice UID, otherwise false.
     */
    private function isPageIdLegalNoticeUid(int $pageUid, int $legalNoticeUid): bool
    {
        return $legalNoticeUid === $pageUid;
    }
}
