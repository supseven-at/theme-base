<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Tests\Unit\ViewHelpers\Render;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Supseven\ThemeBase\ViewHelpers\Render\InlineSvgViewHelper;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * InlineSvgViewHelperTest
 */
final class InlineSvgViewHelperTest extends UnitTestCase
{
    /**
     * Reset singleton instances
     *
     * @var bool $resetSingletonInstances
     */
    protected bool $resetSingletonInstances = true;

    /**
     * @var string $fileName
     */
    private string $fileName = 'image.svg';

    /**
     * @var string $svgContent
     */
    private string $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" id="test-id">
              <style>
                circle {
                  fill: gold;
                  stroke: maroon;
                  stroke-width: 2px;
                }
              </style>
            <circle cx="50" cy="50" r="40" stroke="black" stroke-width="3" fill="red" />
        </svg>';

    /**
     * @var string $rootPath
     */
    private string $rootPath = 'vfs://root';

    /**
     * @var vfsStreamDirectory $root
     */
    private vfsStreamDirectory $root;

    /**
     * @var vfsStreamFile $file
     */
    private vfsStreamFile $file;

    public function setUp(): void
    {
        parent::setUp();

        $applicationContext = new ApplicationContext('Testing');

        $this->root = vfsStream::setup();
        $this->file = vfsStream::newFile($this->fileName)->at($this->root);
        $this->file->setContent($this->svgContent);

        Environment::initialize(
            $applicationContext,
            true,
            true,
            $this->rootPath,
            $this->rootPath,
            '/app/var',
            '/app/config',
            '/app',
            'Linux'
        );
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[DataProvider('renderStaticDataProvider')]
    #[Test]
    public function render(array $arguments, array $expectedResult): void
    {
        $arguments = [
            'id'                => $arguments['id'] ?? null,
            'uniqueId'          => $arguments['uniqueId'] ?? null,
            'title'             => $arguments['title'] ?? null,
            'source'            => $this->fileName,
            'remove-styles'     => $arguments['remove-styles'] ?? false,
            'move-styles'       => false,
            'fill'              => $arguments['fill'] ?? '#ffffff',
            'custom-tags'       => [],
            'custom-attributes' => [],
            'class'             => $arguments['class'] ?? null,
            'width'             => $arguments['width'] ?? null,
            'height'            => $arguments['height'] ?? null,
        ];

        $renderChildrenClosure = fn () => null;

        $renderingContext = $this->getTypoScriptFrontendControllerMock();

        $packageManager = self::createMock(PackageManager::class);
        $packageManager->expects(self::never())->method('resolvePackagePath');
        $assetCollector = $this->createMock(AssetCollector::class);

        $subject = new InlineSvgViewHelper($packageManager, $assetCollector);
        $subject->setArguments($arguments);
        $subject->setRenderingContext($renderingContext);
        $subject->setRenderChildrenClosure($renderChildrenClosure);
        $sut = $subject->render();

        if (isset($expectedResult['misses'])) {
            self::assertStringNotContainsString($expectedResult['misses'], $sut);
        }

        if (isset($expectedResult['contains'])) {
            self::assertStringContainsString($expectedResult['contains'], $sut);
        }
    }

    /**
     * @throws \TYPO3\CMS\Core\Package\Exception\UnknownPackageException
     * @throws \TYPO3\CMS\Core\Package\Exception\UnknownPackagePathException
     */
    #[Test]
    public function getFilePath(): void
    {
        $packageManager = self::createMock(PackageManager::class);
        $assetCollector = $this->createMock(AssetCollector::class);

        $subject = new InlineSvgViewHelper($packageManager, $assetCollector);
        $result = $subject->getFilePath($this->fileName);

        self::assertSame($this->rootPath . '/' . $this->fileName, $result);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \TYPO3\CMS\Core\Package\Exception\UnknownPackageException
     * @throws \TYPO3\CMS\Core\Package\Exception\UnknownPackagePathException
     */
    #[Test]
    public function getFilePathWithExt(): void
    {
        $packageManager = self::createMock(PackageManager::class);
        $packageManager->expects(self::once())
            ->method('resolvePackagePath')
            ->with('EXT:theme/Resources/Public/Images/' . $this->fileName)
            ->willReturn('foobar');

        $assetCollector = $this->createMock(AssetCollector::class);

        $subject = new InlineSvgViewHelper($packageManager, $assetCollector);

        $result = $subject->getFilePath('EXT:theme/Resources/Public/Images/' . $this->fileName);

        // Assert, condition 'EXT' is true
        self::assertStringNotContainsString('.svg', $result);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     */
    #[Test]
    public function renderThrowsFileDoesNotExistException(): void
    {
        self::expectException(FileDoesNotExistException::class);

        $arguments = [
            'source' => 'not-existing.svg',
        ];

        $renderChildrenClosure = fn () => null;

        $packageManager = self::createMock(PackageManager::class);
        $packageManager->expects(self::never())
            ->method('resolvePackagePath')
            ->with('EXT:theme/Resources/Public/Images/' . $this->fileName)
            ->willReturn('foobar');

        $assetCollector = $this->createMock(AssetCollector::class);

        $renderingContext = $this->getTypoScriptFrontendControllerMock();

        $subject = new InlineSvgViewHelper($packageManager, $assetCollector);
        $subject->setArguments($arguments);
        $subject->setRenderChildrenClosure($renderChildrenClosure);
        $subject->setRenderingContext($renderingContext);

        $subject->render();
    }

    /**
     * @param string $svgIdString
     * @param string $expected
     */
    #[DataProvider('sanitizeIdDataProvider')]
    #[Test]
    public function sanitizeId(string $svgIdString, string $expected): void
    {
        $packageManager = self::createMock(PackageManager::class);
        $assetCollector = $this->createMock(AssetCollector::class);

        $subject = new InlineSvgViewHelper($packageManager, $assetCollector);
        $sut = $subject->sanitizeId($svgIdString);

        self::assertSame($expected, $sut);
    }

    /**
     * Dataprovider for renderStatic test method
     *
     * @return array[]
     */
    public static function renderStaticDataProvider(): array
    {
        return [
            'no arguments' => [
                [],
                [
                    'contains' => '<svg xmlns="http://www.w3.org/2000/svg',
                ],
            ],
            'remove styles' => [
                ['remove-styles' => true],
                [
                    'misses' => 'style',
                ],
            ],
            'set title' => [
                ['title' => 'SVG Title'],
                [
                    'contains' => '<title>SVG Title</title>',
                ],
            ],
            'set fill color' => [
                ['fill' => '#000000'],
                [
                    'misses'   => 'fill="#red"',
                    'contains' => 'fill="#000000"',
                ],
            ],
            'set id without title' => [
                ['id' => 'asdf'],
                [
                    'contains' => '<!-- This SVG is not accessible, if there is no Title attribute available -->',
                ],
            ],
            'set id' => [
                [
                    'id'    => 'asdf',
                    'title' => 'SVG Title',
                ],
                [
                    'misses'   => '<!-- This SVG is not accessible, if there is no Title attribute available -->',
                    'contains' => 'aria-labelledby="asdf"',
                ],
            ],
            'set unique-id' => [
                [
                    'id'       => 'asdf',
                    'uniqueId' => 'sdfg',
                    'title'    => 'SVG Title',
                ],
                [
                    'misses'   => 'id="test-id"',
                    'contains' => 'id="svg-sdfg"',
                ],
            ],
            'set class' => [
                [
                    'class' => 'test-class',
                ],
                [
                    'contains' => 'class="test-class"',
                ],
            ],
            'set width larger 0' => [
                [
                    'width' => 100,
                ],
                [
                    'contains' => 'width="100"',
                ],
            ],
            'set width = 0' => [
                [
                    'width' => 0,
                ],
                [
                    'missing' => 'width=',
                ],
            ],
            'set height larger 0' => [
                [
                    'height' => 100,
                ],
                [
                    'contains' => 'height="100"',
                ],
            ],
            'set height = 0' => [
                [
                    'height' => 0,
                ],
                [
                    'missing' => 'height=',
                ],
            ],
        ];
    }

    /**
     * DataProvider for sanitizeId test
     *
     * @return array[]
     */
    public static function sanitizeIdDataProvider(): array
    {
        return [
            'empty'                      => ['', ''],
            'no special chars'           => ['abcd', 'abcd'],
            'no digit at the beginning'  => ['0abcd', 'xabcd'],
            'replace special characters' => ['aäöbü*c+#d', 'a____b___c__d'],
        ];
    }

    /**
     * @return \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function getTypoScriptFrontendControllerMock(): RenderingContext
    {
        $typoScriptFrontendController = $this->createMock(TypoScriptFrontendController::class);
        $request = (new ServerRequest())->withAttribute('frontend.controller', $typoScriptFrontendController);
        $renderingContext = $this->createMock(RenderingContext::class);
        $renderingContext->method('getAttribute')->willReturn($request);

        return $renderingContext;
    }
}
