<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * StrlenViewHelper extends AbstractViewHelper and provides a static method to count the length of a string.
 *
 *  = Example =
 *  <theme:strlen>{item.record.bodytext -> f:format.stripTags()}</theme:strlen>
 */
class StrlenViewHelper extends AbstractViewHelper
{
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

    public function getContentArgumentName(): ?string
    {
        return 'string';
    }

    /**
     * @return int
     */
    public function render(): int
    {
        /** @var string $encoding */
        $encoding = $this->arguments['encoding'];
        $content = (string)$this->renderChildren();

        return (int)mb_strlen($content, $encoding);
    }
}
