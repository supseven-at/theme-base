<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Returns only the domain part of a given URL.
 *
 * EXAMPLE:
 *
 * <theme:format.domainName url="https://www.test.com" />
 */
class DomainNameViewHelper extends AbstractViewHelper implements ViewHelperInterface
{
    use CompileWithRenderStatic;

    /** @var bool */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('url', 'string', 'Url where the domain must be extracted', true);
    }

    /**
     * Renders the static content by calling the parseUrl method.
     *
     * @param array $arguments The arguments passed to the renderStatic method.
     * @param \Closure $renderChildrenClosure The closure for rendering children.
     * @param RenderingContextInterface $renderingContext The rendering context.
     * @return string The extracted domain name or 'domainName-extraction-not-possible' if an exception occurred.
     * @throws \Exception If an exception occurs while calling the parseUrl method.
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        return self::parseUrl($arguments['url'], $arguments);
    }

    /**
     * Parses a URL and extracts the domain name.
     *
     * @param string $url The URL to parse.
     * @param array $arguments Additional arguments.
     * @return string The extracted domain name.
     * @throws \Exception If the URL does not have a valid domain name.
     */
    protected static function parseUrl(string $url, array $arguments): string
    {
        try {
            $url = parse_url($arguments['url']);
            $host = $url['host'] ?? $url['path'];

            $parts = explode('.', $host);

            if (count($parts) < 2) {
                throw new \Exception('No valid domain name: ' . $arguments['url'], 1712841578);
            }

            return $parts[count($parts) - 2];
        } catch (\Exception $e) {
            return $e->getCode() . ': ' . $e->getMessage();
        }
    }
}
