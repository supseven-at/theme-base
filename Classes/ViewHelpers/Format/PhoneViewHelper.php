<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Class PhoneViewHelper
 *
 * A view helper class for formatting phone numbers for the correct usage in tel: handlers
 *
 * EXAMPLE:
 * <a href="tel:{address.phone -> t:format.phone()}" class="text-decoration-none">{address.phone}</a>
 *
 * NOTE:
 * we might add this to an event.
 */
class PhoneViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    protected $escapeChildren = false;

    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('number', 'string', '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $content = (string)$renderChildrenClosure();
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        $content = trim($content);
        $hasPlus = str_starts_with($content, '+');

        if (strpos($content, '(0)-') >= 0) {
            $content = str_replace('(0)', '', $content);
        }

        $content = preg_replace('/[^\\d]+/', '', $content);

        if ($hasPlus) {
            $content = '00' . $content;
        }

        return $content;
    }
}
