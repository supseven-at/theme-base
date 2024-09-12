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
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
    private string $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" stroke="black" stroke-width="3" fill="red" /></svg>';

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

        $applicationContext = new ApplicationContext('Development');
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

        $this->root = vfsStream::setup();

        $this->file = vfsStream::newFile($this->fileName)
            ->at($this->root);
        $this->file->setContent($this->svgContent);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function renderStatic(): void
    {
        $arguments = [
            'source'            => $this->fileName,
            'remove-styles'     => false,
            'fill'              => 'red',
            'uniqueId'          => 'uniqueId',
            'custom-tags'       => [],
            'custom-attributes' => [],
        ];

        $renderChildrenClosure = fn () => null;

        $renderingContext = $this->getTypoScriptFrontendControllerMock();

        $sut = InlineSvgViewHelper::renderStatic($arguments, $renderChildrenClosure, $renderingContext);

        self::assertStringContainsString('<svg xmlns="http://www.w3.org/2000/svg"', $sut);
    }

    /**
     * @return void
     * @throws \TYPO3\CMS\Core\Package\Exception\UnknownPackageException
     * @throws \TYPO3\CMS\Core\Package\Exception\UnknownPackagePathException
     */
    #[Test]
    public function getFilePath(): void
    {
        $result = InlineSvgViewHelper::getFilePath($this->fileName);

        self::assertSame($this->rootPath . '/' . $this->fileName, $result);
    }

    /**
     * @return void
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

        // Prevent the actual class from being instantiated in the sut:
        GeneralUtility::setSingletonInstance(PackageManager::class, $packageManager);

        $result = InlineSvgViewHelper::getFilePath('EXT:theme/Resources/Public/Images/' . $this->fileName);

        // Assert, condition 'EXT' is true
        self::assertStringNotContainsString('.svg', $result);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     */
    #[Test]
    public function renderStaticThrowsFileDoesNotExistException(): void
    {
        self::expectException(FileDoesNotExistException::class);

        $arguments = [
            'source' => 'not-existing.svg',
        ];

        $renderChildrenClosure = fn () => null;

        $renderingContext = $this->getTypoScriptFrontendControllerMock();

        $sut = InlineSvgViewHelper::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }

    /**
     * @param string $svgIdString
     * @param string $expected
     */
    #[DataProvider('sanitizeIdDataProvider')]
    #[Test]
    public function sanitizeId(string $svgIdString, string $expected): void
    {
        $sut = InlineSvgViewHelper::sanitizeId($svgIdString);

        self::assertSame($expected, $sut);
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
        $renderingContext->method('getRequest')->willReturn($request);

        return $renderingContext;
    }
}
