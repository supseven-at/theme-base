<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class CleanupStringViewHelper
 *
 * This view helper is used to clean up strings by eliminating whitespace characters, tabs, line breaks,
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
     * @return string
     */
    public function render(): string
    {
        $content = $this->renderChildren();

        if (true === $this->arguments['whitespace']) {
            $content = $this->eliminateWhitespace($content);
        }

        if (true === $this->arguments['tabs']) {
            $content = $this->eliminateTabs($content);
        }

        if (true === $this->arguments['lineBreaks']) {
            $content = $this->eliminateLineBreaks($content);
        }

        if (true === $this->arguments['unixBreaks']) {
            $content = $this->eliminateUnixBreaks($content);
        }

        if (true === $this->arguments['windowsBreaks']) {
            $content = $this->eliminateWindowsCarriageReturns($content);
        }

        return $content;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function eliminateWhitespace(string $content): string
    {
        return (string)preg_replace('/\\s+/', ' ', $content);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function eliminateTabs(string $content): string
    {
        return str_replace("\t", '', $content);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function eliminateLineBreaks(string $content): string
    {
        return str_replace("\n\r", '', $content);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function eliminateUnixBreaks(string $content): string
    {
        return str_replace("\n", '', $content);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function eliminateWindowsCarriageReturns(string $content): string
    {
        return str_replace("\r", '', $content);
    }
}
