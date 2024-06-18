<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Class CleanupStringViewHelper
 *
 * This view helper is used to cleanup strings by eliminating whitespace characters, tabs, line breaks,
 * UNIX line breaks, and Windows carriage returns.
 *
 * EXAMPLE:
 *
 * <theme:format.cleanupString tabs="true" lineBreaks="true" unixBreaks="true" windowsBreaks="true">
 * only screen and ({breakpoint.media -> f:or(alternative: 'min-width')}: {breakpoint.size}px) and (-webkit-min-device-pixel-ratio: {pd.min-ratio}),
 * only screen and ({breakpoint.media -> f:or(alternative: 'min-width')}: {breakpoint.size}px) and (min-resolution: {pd.min-resolution}dpi)
 * </theme:format.cleanupString>
 */
class CleanupStringViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('content', 'string', 'String to cleanup');
        $this->registerArgument('whitespace', 'boolean', 'Eliminate ALL whitespace characters', false, false);
        $this->registerArgument('tabs', 'boolean', 'Eliminate only tab whitespaces', false, false);
        $this->registerArgument('lineBreaks', 'boolean', 'Eliminate combined line breaks', false, false);
        $this->registerArgument('unixBreaks', 'boolean', 'Eliminate only UNIX line breaks', false, false);
        $this->registerArgument('windowsBreaks', 'boolean', 'Eliminates only Windows carriage returns', false, false);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $content = $renderChildrenClosure();

        if (true === $arguments['whitespace']) {
            $content = static::eliminateWhitespace($content);
        }

        if (true === $arguments['tabs']) {
            $content = static::eliminateTabs($content);
        }

        if (true === $arguments['lineBreaks']) {
            $content = static::eliminateLineBreaks($content);
        }

        if (true === $arguments['unixBreaks']) {
            $content = static::eliminateUnixBreaks($content);
        }

        if (true === $arguments['windowsBreaks']) {
            $content = static::eliminateWindowsCarriageReturns($content);
        }

        return $content;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected static function eliminateWhitespace(string $content): string
    {
        return (string)preg_replace('/\\s+/', ' ', $content);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected static function eliminateTabs(string $content): string
    {
        return str_replace("\t", '', $content);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected static function eliminateLineBreaks(string $content): string
    {
        return str_replace("\n\r", '', $content);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected static function eliminateUnixBreaks(string $content): string
    {
        return str_replace("\n", '', $content);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected static function eliminateWindowsCarriageReturns(string $content): string
    {
        return str_replace("\r", '', $content);
    }
}
