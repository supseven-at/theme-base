<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
    public function initializeArguments(): void
    {
        $this->registerArgument('content', 'string', 'Content to Explode', false);
    }

    public function getContentArgumentName(): ?string
    {
        return 'content';
    }

    public function render(): array
    {
        $content = $this->renderChildren();
        $content = explode("\n", (string)$content);

        if (\count($content) > 0) {
            return $content;
        }

        return [
            $content,
        ];
    }
}
