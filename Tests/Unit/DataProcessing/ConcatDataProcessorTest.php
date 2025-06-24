<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Tests\Unit\DataProcessing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Supseven\ThemeBase\DataProcessing\ConcatDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class ConcatDataProcessorTest extends TestCase
{
    #[Test]
    public function process(): void
    {
        $config = [
            'as'      => 'list',
            'glue'    => ',',
            'sort'    => '1',
            'fields.' => [
                'backend_layout' => 'pagets_Startsite ? homepage : default',
                'title'          => 'Main ? main : other',
            ],
        ];

        $data = [
            'data' => [
                'uid'            => 12,
                'backend_layout' => 'pagets_Other',
                'title'          => 'Main',
            ],
        ];

        $cObj = $this->createMock(ContentObjectRenderer::class);
        $cObj->method('stdWrapValue')->willReturnMap([
            ['as', $config, $config['as']],
            ['glue', $config, ' ', $config['glue']],
            ['sort', $config, true, $config['sort']],
        ]);

        $subject = new ConcatDataProcessor();

        $expected = $data;
        $expected[$config['as']] = 'default,main';

        $actual = $subject->process($cObj, [], $config, $data);

        $this->assertEquals($expected, $actual);
    }
}
