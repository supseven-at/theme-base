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
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3\CMS\Core\Resource\ProcessedFile;

/**
 * Test for Supseven\ThemeBase\ViewHelpers\Render\ImageRenderViewHelper
 */
final class ImageRenderViewHelperTest extends TestCase
{
    /**
     * Test the whole render method
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function render(): void
    {
        $maxWidth = 1000;
        $size = '576';
        $fileExtension = 'jpg';
        $filePath = 'fileadmin/someFile.jpg';
        $imgClass = 'img-fluid';
        $minRatio = 2;
        $loading = 'lazy';

        $fileReferenceMock = self::createMock(FileReference::class);
        $fileReferenceMock->expects(self::any())
            ->method('getProperty')
            ->willReturn('');

        $processedFileMock = self::createMock(ProcessedFile::class);

        $imageServiceMock = self::createMock(ImageService::class);
        $imageServiceMock->expects(self::any())
            ->method('applyProcessingInstructions')
            ->willReturn($processedFileMock);
        $imageServiceMock->expects(self::any())
            ->method('getImageUri')
            ->willReturn($filePath);

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
        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = $fileExtension . ',jpeg,png,gif,svg';

        $subject->setRenderingContext($renderingContextMock);

        $subject->setArguments([
            'image' => $fileReferenceMock,
            'breakpoints' => 'default',
            'fileExtension' => $fileExtension,
            'imgClass' => $imgClass,
            'loading' => $loading,
            'settings' => [
                'breakpoints' => [
                    'default' => [
                        0 =>[
                            'media' => 'min-width',
                            'size' => $size,
                            'maxWidth' => $maxWidth,
                            'cropVariant' => 'xs',
                        ],
                        1 =>[
                            'media' => 'min-width',
                            'size' => $size*2,
                            'maxWidth' => $maxWidth*2,
                            'cropVariant' => 'xs',
                        ],
                    ],
                    'pixelDensities' => [
                        0 => [
                            'min-ratio' => $minRatio,
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

        self::assertIsString($result);
        self::assertStringContainsString('<picture><source srcset="', $result);
        self::assertStringContainsString($filePath, $result);
        self::assertStringContainsString('only screen and (min-width: ' . $size . 'px)', $result);
        self::assertStringContainsString('only screen and (min-width: ' . $size*(int)$minRatio . 'px)', $result);
        self::assertStringContainsString('class="' . $imgClass . '"', $result);
        self::assertStringContainsString('loading="' . $loading . '"', $result);

    }
}
