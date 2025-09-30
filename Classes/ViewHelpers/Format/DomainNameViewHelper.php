<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Format;

use TYPO3\CMS\Core\Http\Uri;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns only the domain part of a given URL.
 *
 * EXAMPLE:
 *
 * <theme:format.domainName url="https://www.test.com" />
 */
class DomainNameViewHelper extends AbstractViewHelper
{
    /** @var bool */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('url', 'string', 'Url where the domain must be extracted', true);
    }

    /**
     * Renders the static content by calling the parseUrl method.
     *
     * @return string The extracted domain name or 'domainName-extraction-not-possible' if an exception occurred.
     */
    public function render(): string
    {
        return new Uri($this->arguments['url'])->getHost();
    }
}
