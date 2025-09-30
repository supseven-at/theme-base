<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Format;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * InlineCssViewHelper class
 *
 * this viewhelper adds inline css with nonce attributes to the page header.
 *
 * Mostly used, if you need a separate above the fold css.
 *
 * EXAMPLE:
 *
 * <theme:format.inlineCss>
 * body { background: red !important; }
 * </theme:format.inlineCss>
 *
 * <theme:format.inlineCss file="EXT:theme_project/Resources/Public/Css/critical.css" />
 */
class InlineCssViewHelper extends AbstractViewHelper
{
    public function __construct(
        protected readonly PackageManager $packageManager,
        protected readonly PageRenderer $pageRenderer,
    ) {
    }

    /**
     * Initialize
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'The Name of the CSS to include. Should be unique');
        $this->registerArgument('file', 'string', 'The css file to include, if no inline css is used');
        $this->registerArgument('compress', 'bool', 'compress the inlined css?', false, false);
        $this->registerArgument('forceOnTop', 'bool', 'force included css on top?', false, false);
    }

    public function render(): void
    {
        $file = null;

        if (isset($this->arguments['file'])) {
            if (stripos($this->arguments['file'], 'EXT:') !== false) {
                $file = $this->packageManager->resolvePackagePath($this->arguments['file']);
            } else {
                $file = Environment::getPublicPath() . '/' . $this->arguments['file'];
            }
        }

        if (null === $file) {
            $content = $this->renderChildren();
        } else {
            $content = is_file($file) ? file_get_contents($file) : null;
        }

        $this->pageRenderer->addCssInlineBlock(
            $this->arguments['name'] ?: hash('xxh3', $content),
            $content,
            $this->arguments['compress'],
            $this->arguments['forceOnTop'],
            true,
        );
    }
}
