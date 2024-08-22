<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Supseven\ThemeBase\Tests\ViewHelpers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Supseven\ThemeBase\ViewHelpers\Render\ImageRenderViewHelper;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * ImageRenderViewHelperTest
 */
final class ImageRenderViewHelperTest extends TestCase
{
    #[Test]
    public function render(): void
    {
        $imageServiceMock = self::createMock(ImageService::class);

        $fileReferenceMock = self::createMock(FileReference::class);
        $fileReferenceMock->expects(self::any())
            ->method('getProperty')
            ->willReturn('');

        $subject = new ImageRenderViewHelper($imageServiceMock);

        $contentObjectRendererMock = self::createMock(ContentObjectRenderer::class);
        $contentObjectRendererMock->data = [
            'image_zoom' => '0',
        ];

        $serverRequestMock = self::createMock(ServerRequest::class);
        $serverRequestMock->expects(self::any())
            ->method('getAttribute')
            ->with('currentContentObject')
            ->willReturn($contentObjectRendererMock);

        $renderingContextMock = self::createMock(RenderingContext::class);
        $renderingContextMock->expects(self::once())
            ->method('getVariableProvider')
            ->willReturn(self::createStub(VariableProviderInterface::class));
        $renderingContextMock->expects(self::once())
            ->method('getViewHelperVariableContainer')
            ->willReturn(self::createStub(ViewHelperVariableContainer::class));
        $renderingContextMock->expects(self::once())
            ->method('getRequest')
            ->willReturn($serverRequestMock);

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['expressionNodeTypes'] = [
            'TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\CastingExpressionNode',
            'TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode',
            'TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode',
        ];
        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = 'jpg,jpeg,png,gif,svg';

        $subject->setRenderingContext($renderingContextMock);

        $subject->setArguments([
            'image' => $fileReferenceMock,
            'breakpoints' => 'default',
            'fileExtension' => 'jpg',
            'imgClass' => 'img-fluid',
            'loading' => 'lazy',
            'settings' => [
                'breakpoints' => [
                    'default' => [
                        0 => [
                            'media' => 'min-width',
                            'size' => '0',
                            'maxWidth' => '543',
                            'cropVariant' => 'xs',
                        ],
                        1 =>[
                            'media' => 'min-width',
                            'size' => '576',
                            'maxWidth' => '735',
                            'cropVariant' => 'xs',
                        ],
                    ],
                    'pixelDensities' => [
                        0 => [
                            'min-ratio' => '2.0',
                            'min-resolution' => '192',
                        ],
                    ],
                ],
            ],
            'cropString' => '',
            'contentObjectData' => [],
        ]);

        $subject->initialize();

        $result = $subject->render();

        self::assertStringContainsString('srcset', $result);
    }
}
