<?php
declare(strict_types=1);

namespace PHPSTORM_META {
    expectedArguments(
        \TYPO3\CMS\Core\Context\Context::getAspect(),
        0,
        'date',
        'visibility',
        'backend.user',
        'frontend.user',
        'workspace',
        'language',
        'frontend.preview',
    );
    override(\TYPO3\CMS\Core\Context\Context::getAspect(), map([
        'date' => \TYPO3\CMS\Core\Context\DateTimeAspect::class,
        'visibility' => \TYPO3\CMS\Core\Context\VisibilityAspect::class,
        'backend.user' => \TYPO3\CMS\Core\Context\UserAspect::class,
        'frontend.user' => \TYPO3\CMS\Core\Context\UserAspect::class,
        'workspace' => \TYPO3\CMS\Core\Context\WorkspaceAspect::class,
        'language' => \TYPO3\CMS\Core\Context\LanguageAspect::class,
        'frontend.preview' => \TYPO3\CMS\Frontend\Context\PreviewAspect::class,
    ]));
    expectedArguments(
        \TYPO3\CMS\Core\Context\DateTimeAspect::get(),
        0,
        'timestamp',
        'iso',
        'timezone',
        'full',
        'accessTime'
    );
    expectedArguments(
        \TYPO3\CMS\Core\Context\VisibilityAspect::get(),
        0,
        'includeHiddenPages',
        'includeHiddenContent',
        'includeDeletedRecords'
    );
    expectedArguments(
        \TYPO3\CMS\Core\Context\UserAspect::get(),
        0,
        'id',
        'username',
        'isLoggedIn',
        'isAdmin',
        'groupIds',
        'groupNames'
    );
    expectedArguments(
        \TYPO3\CMS\Core\Context\WorkspaceAspect::get(),
        0,
        'id',
        'isLive',
        'isOffline'
    );
    expectedArguments(
        \TYPO3\CMS\Core\Context\LanguageAspect::get(),
        0,
        'id',
        'contentId',
        'fallbackChain',
        'overlayType',
        'legacyLanguageMode',
        'legacyOverlayType'
    );
    expectedArguments(
        \TYPO3\CMS\Frontend\Context\PreviewAspect::get(),
        0,
        'isPreview'
    );

    expectedArguments(
        \Psr\Http\Message\ServerRequestInterface::getAttribute(),
        0,
        'frontend.user',
        'normalizedParams',
        'site',
        'language',
        'routing',
        'module',
        'moduleData',
        'currentContentObject',
        'frontend.controller',
        'frontend.typoscript',
        'frontend.cache.collector',
        'frontend.cache.instruction',
        'frontend.page.information',
    );
    override(\Psr\Http\Message\ServerRequestInterface::getAttribute(), map([
        'frontend.user' => \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class,
        'normalizedParams' => \TYPO3\CMS\Core\Http\NormalizedParams::class,
        'site' => \TYPO3\CMS\Core\Site\Entity\Site::class,
        'language' => \TYPO3\CMS\Core\Site\Entity\SiteLanguage::class,
        'routing' => '\TYPO3\CMS\Core\Routing\SiteRouteResult|\TYPO3\CMS\Core\Routing\PageArguments',
        'module' => \TYPO3\CMS\Backend\Module\ModuleInterface::class,
        'moduleData' => \TYPO3\CMS\Backend\Module\ModuleData::class,
        'currentContentObject' => \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class,
        'frontend.controller' => \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::class,
        'frontend.typoscript' => \TYPO3\CMS\Core\TypoScript\FrontendTypoScript::class,
        'frontend.cache.collector' => \TYPO3\CMS\Core\Cache\CacheDataCollector::class,
        'frontend.cache.instruction' => \TYPO3\CMS\Frontend\Cache\CacheInstruction::class,
        'frontend.page.information' => \TYPO3\CMS\Frontend\Page\PageInformation::class,
    ]));

    expectedArguments(
        \TYPO3\CMS\Core\Http\ServerRequest::getAttribute(),
        0,
        'frontend.user',
        'normalizedParams',
        'site',
        'language',
        'routing',
        'module',
        'moduleData',
        'currentContentObject',
    );
    override(\TYPO3\CMS\Core\Http\ServerRequest::getAttribute(), map([
        'frontend.user' => \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class,
        'normalizedParams' => \TYPO3\CMS\Core\Http\NormalizedParams::class,
        'site' => \TYPO3\CMS\Core\Site\Entity\Site::class,
        'language' => \TYPO3\CMS\Core\Site\Entity\SiteLanguage::class,
        'routing' => '\TYPO3\CMS\Core\Routing\SiteRouteResult|\TYPO3\CMS\Core\Routing\PageArguments',
        'module' => \TYPO3\CMS\Backend\Module\ModuleInterface::class,
        'moduleData' => \TYPO3\CMS\Backend\Module\ModuleData::class,
        'currentContentObject' => \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class,
    ]));


    expectedArguments(
        \TYPO3\CMS\Extbase\Mvc\RequestInterface::getAttribute(),
        0,
        'frontend.user',
        'normalizedParams',
        'site',
        'language',
        'routing',
        'module',
        'moduleData',
        'currentContentObject',
        'extbase',
        'frontend.controller',
        'frontend.typoscript',
        'frontend.cache.collector',
        'frontend.cache.instruction',
        'frontend.page.information',
    );
    override(\TYPO3\CMS\Extbase\Mvc\RequestInterface::getAttribute(), map([
        'frontend.user' => \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class,
        'normalizedParams' => \TYPO3\CMS\Core\Http\NormalizedParams::class,
        'site' => \TYPO3\CMS\Core\Site\Entity\Site::class,
        'language' => \TYPO3\CMS\Core\Site\Entity\SiteLanguage::class,
        'routing' => '\TYPO3\CMS\Core\Routing\SiteRouteResult|\TYPO3\CMS\Core\Routing\PageArguments',
        'module' => \TYPO3\CMS\Backend\Module\ModuleInterface::class,
        'moduleData' => \TYPO3\CMS\Backend\Module\ModuleData::class,
        'currentContentObject' => \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class,
        'extbase' => \TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters::class,
        'frontend.controller' => \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::class,
        'frontend.typoscript' => \TYPO3\CMS\Core\TypoScript\FrontendTypoScript::class,
        'frontend.cache.collector' => \TYPO3\CMS\Core\Cache\CacheDataCollector::class,
        'frontend.cache.instruction' => \TYPO3\CMS\Frontend\Cache\CacheInstruction::class,
        'frontend.page.information' => \TYPO3\CMS\Frontend\Page\PageInformation::class,
    ]));
    expectedArguments(
        \TYPO3\CMS\Extbase\Mvc\Request::getAttribute(),
        0,
        'frontend.user',
        'normalizedParams',
        'site',
        'language',
        'routing',
        'module',
        'moduleData',
        'currentContentObject',
        'extbase',
        'frontend.controller',
        'frontend.typoscript',
        'frontend.cache.collector',
        'frontend.cache.instruction',
        'frontend.page.information',
    );
    override(\TYPO3\CMS\Extbase\Mvc\Request::getAttribute(), map([
        'frontend.user' => \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class,
        'normalizedParams' => \TYPO3\CMS\Core\Http\NormalizedParams::class,
        'site' => \TYPO3\CMS\Core\Site\Entity\SiteInterface::class,
        'language' => \TYPO3\CMS\Core\Site\Entity\SiteLanguage::class,
        'routing' => '\TYPO3\CMS\Core\Routing\SiteRouteResult|\TYPO3\CMS\Core\Routing\PageArguments',
        'module' => \TYPO3\CMS\Backend\Module\ModuleInterface::class,
        'moduleData' => \TYPO3\CMS\Backend\Module\ModuleData::class,
        'currentContentObject' => \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class,
        'extbase' => \TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters::class,
        'frontend.controller' => \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::class,
        'frontend.typoscript' => \TYPO3\CMS\Core\TypoScript\FrontendTypoScript::class,
        'frontend.cache.collector' => \TYPO3\CMS\Core\Cache\CacheDataCollector::class,
        'frontend.cache.instruction' => \TYPO3\CMS\Frontend\Cache\CacheInstruction::class,
        'frontend.page.information' => \TYPO3\CMS\Frontend\Page\PageInformation::class,
    ]));

    override(\TYPO3\CMS\Core\Routing\SiteMatcher::matchRequest(), type(
            \TYPO3\CMS\Core\Routing\SiteRouteResult::class,
            \TYPO3\CMS\Core\Routing\RouteResultInterface::class,
        )
    );

    override(\TYPO3\CMS\Core\Routing\PageRouter::matchRequest(), type(
        \TYPO3\CMS\Core\Routing\PageArguments::class,
        \TYPO3\CMS\Core\Routing\RouteResultInterface::class,
    ));

    override(\Psr\Container\ContainerInterface::get(0), map([
        '@',
    ]));

    override(\Psr\EventDispatcher\EventDispatcherInterface::dispatch(0), map([
        '@',
    ]));

    override(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(0), map([
        '@'
    ]));

    override(
        \TYPO3\TestingFramework\Core\BaseTestCase::getAccessibleMock(0),
        map([
            '@&\PHPUnit\Framework\MockObject\MockObject',
        ])
    );

    override(
        \PHPUnit\Framework\TestCase::createMock(0),
        map([
            '@&\PHPUnit\Framework\MockObject\MockObject',
        ])
    );

    override(
        \PHPUnit\Framework\TestCase::createStub(0),
        map([
            '@&\PHPUnit\Framework\MockObject\Stub',
        ])
    );

    override(
        \PHPUnit\Framework\TestCase::createConfiguredMock(0),
        map([
            '@&\PHPUnit\Framework\MockObject\MockObject',
        ])
    );

    override(
        \PHPUnit\Framework\TestCase::createPartialMock(0),
        map([
            '@&\PHPUnit\Framework\MockObject\MockObject',
        ])
    );

    override(
        \PHPUnit\Framework\TestCase::createTestProxy(0),
        map([
            '@&\PHPUnit\Framework\MockObject\MockObject',
        ])
    );

    override(
        \PHPUnit\Framework\TestCase::getMockForAbstractClass(0),
        map([
            '@&\PHPUnit\Framework\MockObject\MockObject',
        ])
    );
}
