<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Class Nl2ArrayViewHelper
 *
 * Helper class to explode a string into an array using newline character.
 *
 * Example:
 *
 * <theme:format.nl2Array>{data.rowDescription}</theme:format.nl2Array>
 */
class Nl2ArrayViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('content', 'string', 'Content to Explode', false);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): array
    {
        $content = $renderChildrenClosure();
        $content = explode("\n", (string)$content);

        if (\count($content) > 0) {
            return $content;
        }

        return [
            $content,
        ];
    }
}
