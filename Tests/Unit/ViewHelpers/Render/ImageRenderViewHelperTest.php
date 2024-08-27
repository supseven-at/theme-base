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
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * Test for Supseven\ThemeBase\ViewHelpers\Render\ImageRenderViewHelper
 */
final class ImageRenderViewHelperTest extends TestCase
{
    /** @var \Supseven\ThemeBase\ViewHelpers\Render\ImageRenderViewHelper SUT */
    private ?ImageRenderViewHelper $subject = null;

    /** @var string */
    private string $filePath = 'fileadmin/someFile.jpg';

    /** @var string */
    private string $fileExtension = 'jpg';

    /** @var FileReference */
    private FileReference|null $fileReferenceMock = null;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function setUp(): void
    {
        $processedFileMock = self::createMock(ProcessedFile::class);

        $imageServiceMock = self::createMock(ImageService::class);
        $imageServiceMock->expects(self::any())
                         ->method('applyProcessingInstructions')
                         ->willReturn($processedFileMock);
        $imageServiceMock->expects(self::any())
                         ->method('getImageUri')
                         ->willReturn($this->filePath);

        $this->subject = new ImageRenderViewHelper($imageServiceMock);

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
        $renderingContextMock->expects(self::any())
                             ->method('getRequest')
                             ->willReturn($serverRequestMock);

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['expressionNodeTypes'] = [
            'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\Expression\\CastingExpressionNode',
            'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\Expression\\MathExpressionNode',
            'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\Expression\\TernaryExpressionNode',
        ];
        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = $this->fileExtension . ',jpeg,png,gif,svg';

        $this->subject->setRenderingContext($renderingContextMock);

        $this->fileReferenceMock = self::createMock(FileReference::class);
        $this->fileReferenceMock->expects(self::any())
                          ->method('getProperty')
                          ->willReturn('');
    }

    public function tearDown(): void
    {
        unset($this->subject);
    }

    /**
     * Test the whole render method
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function render(): void
    {
        $maxWidth = 1000;
        $size = '576';
        $imgClass = 'img-fluid';
        $minRatio = 2;
        $loading = 'lazy';

        $this->subject->setArguments([
            'image'         => $this->fileReferenceMock,
            'breakpoints'   => 'default',
            'fileExtension' => $this->fileExtension,
            'imgClass'      => $imgClass,
            'loading'       => $loading,
            'settings'      => [
                'breakpoints' => [
                    'default' => [
                        0 => [
                            'media'       => 'min-width',
                            'size'        => $size,
                            'maxWidth'    => $maxWidth,
                            'cropVariant' => 'xs',
                        ],
                        1 => [
                            'media'       => 'min-width',
                            'size'        => $size * 2,
                            'maxWidth'    => $maxWidth * 2,
                            'cropVariant' => 'xs',
                        ],
                    ],
                    'pixelDensities' => [
                        0 => [
                            'min-ratio'      => $minRatio,
                            'min-resolution' => '192',
                        ],
                    ],
                ],
            ],
            'cropString'        => '',
            'contentObjectData' => [],
        ]);

        $this->subject->initialize();

        $result = $this->subject->render();

        self::assertIsString($result);
        self::assertStringContainsString('<picture><source srcset="', $result);
        self::assertStringContainsString($this->filePath, $result);
        self::assertStringContainsString('only screen and (min-width: ' . $size . 'px)', $result);
        self::assertStringContainsString('only screen and (min-width: ' . $size * (int)$minRatio . 'px)', $result);
        self::assertStringContainsString('class="' . $imgClass . '"', $result);
        self::assertStringContainsString('loading="' . $loading . '"', $result);
    }

    /**
     * Test processImage method to throw exception if image extension is not allowed
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function processImageThrowsException(): void
    {
        $maxWidth = 1000;
        $size = '576';
        $imgClass = 'img-fluid';
        $minRatio = 2;
        $loading = 'lazy';

        $this->fileExtension = 'webp';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The extension ' . $this->fileExtension . ' is not specified in $GLOBALS[\'TYPO3_CONF_VARS\'][\'GFX\'][\'imagefile_ext\']'
                                      . ' as a valid image file extension and can not be processed.');
        $this->expectExceptionCode(1618989190);

        $this->subject->setArguments([
            'image'         => $this->fileReferenceMock,
            'breakpoints'   => 'default',
            'fileExtension' => $this->fileExtension,
            'imgClass'      => $imgClass,
            'loading'       => $loading,
            'settings'      => [
                'breakpoints' => [
                    'default' => [
                        0 => [
                            'media'       => 'min-width',
                            'size'        => $size,
                            'maxWidth'    => $maxWidth,
                            'cropVariant' => 'xs',
                        ],
                    ],
                    'pixelDensities' => [
                        0 => [
                            'min-ratio'      => $minRatio,
                            'min-resolution' => '192',
                        ],
                    ],
                ],
            ],
            'cropString'        => '',
            'contentObjectData' => [],
        ]);

        $this->subject->initialize();

        $this->subject->processImage(100, self::createStub(Area::class));
    }
}
