<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Tests\Unit\ViewHelpers\Render;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Supseven\ThemeBase\ViewHelpers\Render\InlineSvgViewHelper;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * InlineSvgViewHelperTest
 */
final class InlineSvgViewHelperTest extends UnitTestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup();
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function renderStatic(): void
    {
        $fileName = 'image.svg';
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" stroke="black" stroke-width="3" fill="red" /></svg>';

        $file = vfsStream::newFile($fileName)
                ->at($this->root);
        $file->setContent($svgContent);

        $applicationContext = new ApplicationContext('Development');
        Environment::initialize(
            $applicationContext,
            true,
            true,
            'vfs://root',
            'vfs://root',
            '/app/var',
            '/app/config',
            '/app',
            'Linux'
        );

        $arguments = [
            'source'            => $fileName,
            'remove-styles'     => false,
            'fill'              => 'red',
            'uniqueId'          => 'uniqueId',
            'custom-tags'       => [],
            'custom-attributes' => [],
        ];

        $renderChildrenClosure = fn () => null;

        $typoScriptFrontendController = $this->createMock(TypoScriptFrontendController::class);
        $request = (new ServerRequest())->withAttribute('frontend.controller', $typoScriptFrontendController);
        $renderingContext = $this->createMock(RenderingContext::class);
        $renderingContext->method('getRequest')->willReturn($request);

        $sut = InlineSvgViewHelper::renderStatic($arguments, $renderChildrenClosure, $renderingContext);

        self::assertStringContainsString('<svg xmlns="http://www.w3.org/2000/svg"', $sut);
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
}
