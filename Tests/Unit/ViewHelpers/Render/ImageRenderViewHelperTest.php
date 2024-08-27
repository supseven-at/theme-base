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

namespace Supseven\ThemeBase\Tests\ViewHelpers\Render;

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

    /** @var int */
    private int $maxWidth = 1000;

    /** @var int */
    private int $size = 576;

    /** @var string */
    private $imgClass = 'img-fluid';

    /** @var int */
    private int $minRatio = 2;

    /** @var string */
    private $loading = 'lazy';

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
        $contentObjectRendererMock->currentRecord = 'tt_content:1';

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

        $this->subject->setArguments([
            'image'         => $this->fileReferenceMock,
            'breakpoints'   => 'default',
            'fileExtension' => $this->fileExtension,
            'imgClass'      => $this->imgClass,
            'loading'       => $this->loading,
            'settings'      => [
                'breakpoints' => [
                    'default' => [
                        0 => [
                            'media'       => 'min-width',
                            'size'        => $this->size,
                            'maxWidth'    => $this->maxWidth,
                            'cropVariant' => 'xs',
                        ],
                        1 => [
                            'media'       => 'min-width',
                            'size'        => $this->size * 2,
                            'maxWidth'    => $this->maxWidth * 2,
                            'cropVariant' => 'xl',
                        ],
                    ],
                    'pixelDensities' => [
                        0 => [
                            'min-ratio'      => $this->minRatio,
                            'min-resolution' => '192',
                        ],
                    ],
                ],
            ],
            'cropString'        => '',
            'contentObjectData' => [],
        ]);
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
        $this->subject->initialize();

        $result = $this->subject->render();

        self::assertIsString($result);
        self::assertStringContainsString('<picture><source srcset="', $result);
        self::assertStringContainsString($this->filePath, $result);
        self::assertStringContainsString('only screen and (min-width: ' . $this->size . 'px)', $result);
        self::assertStringContainsString('only screen and (min-width: ' . ($this->size * $this->minRatio) . 'px)', $result);
        self::assertStringContainsString('class="' . $this->imgClass . '"', $result);
        self::assertStringContainsString('loading="' . $this->loading . '"', $result);
    }

    /**
     * Test processImage method to throw exception if image extension is not allowed
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function processImageThrowsException(): void
    {
        $this->fileExtension = 'webp';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The extension ' . $this->fileExtension . ' is not specified in $GLOBALS[\'TYPO3_CONF_VARS\'][\'GFX\'][\'imagefile_ext\']'
                                      . ' as a valid image file extension and can not be processed.');
        $this->expectExceptionCode(1618989190);

        $this->subject->setArguments([
            'image'         => $this->fileReferenceMock,
            'breakpoints'   => 'default',
            'fileExtension' => $this->fileExtension,
            'settings'      => [
                'breakpoints' => [
                    'default' => [
                    ],
                ],
            ],
            'cropString'        => '',
            'contentObjectData' => [],
        ]);

        $this->subject->initialize();

        $this->subject->processImage(100, self::createStub(Area::class));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function getCroppingArea(): void
    {
        $this->fileReferenceMock = self::createMock(FileReference::class);
        $this->fileReferenceMock->expects(self::any())
                                ->method('getProperty')
                                ->with('crop')
                                ->willReturn('{"xs":{"cropArea":{"x":2,"y":4,"width":6,"height":8},"selectedRatio":"1:1","focusArea":null},"xl":{"cropArea":{"x":0,"y":0.125,"width":1,"height":0.75},"selectedRatio":"4:3","focusArea":null}}');

        $this->subject->setArguments([
            'image'         => $this->fileReferenceMock,
            'breakpoints'   => 'default',
            'fileExtension' => $this->fileExtension,
            'imgClass'      => $this->imgClass,
            'loading'       => $this->loading,
            'settings'      => [
                'breakpoints' => [
                    'default' => [
                    ],
                ],
            ],
            'cropString'        => '',
            'contentObjectData' => [],
        ]);

        $this->subject->initialize();

        $area = $this->subject->getCropping('xs');

        self::assertInstanceOf(Area::class, $area);
        self::assertSame(2.0, $area->getOffsetLeft());
        self::assertSame(4.0, $area->getOffsetTop());
        self::assertSame(6.0, $area->getWidth());
        self::assertSame(8.0, $area->getHeight());

        $area = $this->subject->getCropping('xl');

        self::assertInstanceOf(Area::class, $area);
        self::assertSame(0.0, $area->getOffsetLeft());
        self::assertSame(0.125, $area->getOffsetTop());
        self::assertSame(1.0, $area->getWidth());
        self::assertSame(0.75, $area->getHeight());
    }

    /**
     * @return void
     */
    #[Test]
    public function getSourceElement(): void
    {
        $this->subject->initialize();

        $imgSrc = [
            'test1.png',
            'test2.png 2x',
        ];

        $sourceElement = $this->subject->getSourceElement($imgSrc, 'test3');

        self::assertStringContainsString('srcset', $sourceElement);
        self::assertStringContainsString('test1', $sourceElement);
        self::assertStringContainsString('test2', $sourceElement);
        self::assertStringContainsString('test3', $sourceElement);
    }

    /**
     * @return void
     */
    #[Test]
    public function getExceptionMessage(): void
    {
        $this->subject->initialize();

        $exceptionMessage = $this->subject->getExceptionMessage('message');

        self::assertStringContainsString(
            'Unable to render image tag in "tt_content:1": message',
            $exceptionMessage
        );
    }
}
