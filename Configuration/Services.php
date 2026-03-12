<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Http\Message\ServerRequestInterface;
use Supseven\ThemeBase\Attributes\AsContentObject;
use Supseven\ThemeBase\Attributes\AsDataProcessor;
use Supseven\ThemeBase\Service\DependencyValuesService;
use Supseven\ThemeBase\Service\ErrorPageService;
use Supseven\ThemeBase\Service\LegalNoticeService;
use Supseven\ThemeBase\Service\PageCacheService;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\AutoconfigureFailedException;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder): void {
    $containerBuilder->registerAttributeForAutoconfiguration(
        AsContentObject::class,
        static function (ChildDefinition $definition, AsContentObject $attribute, \ReflectionClass $class): void {
            $parents = [];
            $parent = $class;

            while ($parent) {
                $parents[] = $parent->name;
                $parent = $parent->getParentClass();
            }

            if (!in_array(AbstractContentObject::class, $parents)) {
                throw new AutoconfigureFailedException(sprintf(
                    'Class "%s" must extend "%s" to be configured as a content object',
                    $class->name,
                    AbstractContentObject::class,
                ));
            }

            $definition->setPublic(true);
            $definition->setShared(false);
            $definition->addTag(AsContentObject::TAG_NAME, ['identifier' => $attribute->name]);
        }
    );

    $containerBuilder->registerAttributeForAutoconfiguration(
        AsDataProcessor::class,
        static function (ChildDefinition $definition, AsDataProcessor $attribute, \ReflectionClass $class): void {
            if (!$class->implementsInterface(DataProcessorInterface::class)) {
                throw new AutoconfigureFailedException(sprintf(
                    'Class "%s" must implement "%s" to be configured as a data processor',
                    $class->name,
                    DataProcessorInterface::class,
                ));
            }

            $definition->setPublic(true);
            $definition->setShared($attribute->shared);
            $definition->addTag(AsDataProcessor::TAG_NAME, ['identifier' => $attribute->shortName]);
        }
    );

    $services = $container->services();
    // Set to public by default because the classes are supposed to be
    // loaded by other packages
    $services->defaults()->public()->autowire()->autoconfigure();

    $services->load('Supseven\\ThemeBase\\DataProcessing\\', __DIR__ . '/../Classes/DataProcessing/*')->share();
    $services->load('Supseven\\ThemeBase\\ViewHelpers\\', __DIR__ . '/../Classes/ViewHelpers/*')->share();
    $services->load('Supseven\\ThemeBase\\Hooks\\', __DIR__ . '/../Classes/Hooks/*')->share();

    $services->set(LegalNoticeService::class)->share();
    $services->set(DependencyValuesService::class)->share();

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
        ->autowire(false)
        ->autoconfigure(false);

    $services->set(PageCacheService::class)
        ->share()
        ->lazy();

    $services->set(ErrorPageService::class)
        ->share(false)
        ->autoconfigure(false)
        ->lazy();
};
