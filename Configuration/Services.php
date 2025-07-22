<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Http\Message\ServerRequestInterface;
use Supseven\ThemeBase\Service\DependencyValuesService;
use Supseven\ThemeBase\Service\LegalNoticeService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Localization\LanguageService;

return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder): void {
    $services = $container->services();
    // Set to public by default because the classes are supposed to be
    // loaded by other packages
    $services->defaults()->public()->autowire()->autoconfigure();

    $services->load('Supseven\\ThemeBase\\DataProcessing\\', __DIR__ . '/../Classes/DataProcessing/*');
    $services->load('Supseven\\ThemeBase\\ViewHelpers\\', __DIR__ . '/../Classes/ViewHelpers/*');

    $services->set(LegalNoticeService::class);

    $services->set(DependencyValuesService::class)->public()->share();

    $services->set('typo3.request', ServerRequestInterface::class)
        ->factory([service(DependencyValuesService::class), 'getRequest'])
        ->share(false)
        ->autowire(false)
        ->autoconfigure(false)
        ->lazy()
        ->alias(ServerRequestInterface::class, 'typo3.request');

    $services->set('typo3.app.context', ApplicationContext::class)
        ->factory([service(DependencyValuesService::class), 'getApplicationContext'])
        ->share(false)
        ->autowire(false)
        ->autoconfigure(false)
        ->lazy()
        ->alias(ApplicationContext::class, 'typo3.app.context');

    $services->set('typo3.app.type', ApplicationType::class)
        ->factory([service(DependencyValuesService::class), 'getApplicationType'])
        ->share(false)
        ->autowire(false)
        ->autoconfigure(false)
        ->alias(ApplicationType::class, 'typo3.app.type');

    $services->set('typo3.lang', LanguageService::class)
        ->factory([service(DependencyValuesService::class), 'getLanguageService'])
        ->share(false)
        ->autowire(false)
        ->autoconfigure(false);
};
