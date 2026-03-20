<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\CSP;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Event\PolicyMutatedEvent;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Change CSP setup after all configs are loaded
 *
 * Use TypoScript setting to enable:
 *
 * <code>
 *     # Allow unsafe in directives script-src-elem, style-src-elem and script-src-attr
 *     config.allow_unsafe_csp = 1
 *
 *     # Allow unsafe in specific directives
 *     config.allow_unsafe_csp = script-src, style-src
 * </code>
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
#[AsEventListener('supseven/theme-project-csp', after: 'praetorius/vite-asset-collector-csp')]
class EnsureUnsafeCSPHeaders
{
    public function __invoke(PolicyMutatedEvent $event): void
    {
        $typoscript = $event->request->getAttribute('frontend.typoscript');

        if (!$typoscript instanceof FrontendTypoScript) {
            return;
        }

        $allowUnsafe = $typoscript->getConfigArray()['allow_unsafe_csp'] ?? '';

        if (!$allowUnsafe || !is_scalar($allowUnsafe)) {
            return;
        }

        if ((int)$allowUnsafe === 1) {
            $allowUnsafe =
                Directive::ScriptSrcElem->value . ',' .
                Directive::StyleSrcElem->value . ',' .
                Directive::StyleSrcAttr->value;
        }

        $directiveNames = GeneralUtility::trimExplode(',', (string)$allowUnsafe, true);
        $policy = $event->getCurrentPolicy();

        foreach ($directiveNames as $directiveName) {
            $directive = Directive::tryFrom($directiveName);

            if (!$directive instanceof Directive) {
                throw new \UnexpectedValueException(sprintf('Invalid directive `%s` in `config.allow_unsafe_csp`', $directiveName));
            }

            if ($policy->has($directive)) {
                $policy = $policy->reduce(
                    $directive,
                    SourceKeyword::nonceProxy,
                );
            }

            $policy = $policy->extend(
                $directive,
                SourceKeyword::unsafeInline,
            );
        }

        $event->setCurrentPolicy($policy);
    }
}
