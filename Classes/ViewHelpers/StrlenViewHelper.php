<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * StrlenViewHelper extends AbstractViewHelper and provides a static method to count the length of a string.
 *
 *  = Example =
 *  <theme:strlen>{item.record.bodytext -> f:format.stripTags()}</theme:strlen>
 */
class StrlenViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'String to count, if not provided as tag content');
        $this->registerArgument(
            'encoding',
            'string',
            'Character set encoding of string, e.g. UTF-8 or ISO-8859-1',
            false,
            'UTF-8'
        );
    }

    /**
     * @return int
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        /** @var string $encoding */
        $encoding = $arguments['encoding'];

        return (int)mb_strlen($renderChildrenClosure(), $encoding);
    }
}
