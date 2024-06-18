<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Format;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\Exception\UnknownPackagePathException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * InlineCssViewHelper class
 *
 * this viewhelper adds inline css with nonce attributes to the page header.
 *
 * Mostly used, if you need an separate above the fold css.
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
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * Initialize
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'The Name of the CSS to include. Should be unique', true);
        $this->registerArgument('file', 'string', 'The css file to include, if no inline css is used');
        $this->registerArgument('compress', 'bool', 'compress the inlined css?', false, false);
        $this->registerArgument('forceOnTop', 'bool', 'force included css on top?', false, false);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @throws UnknownPackageException
     * @throws UnknownPackagePathException
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): void {
        $file = null;

        if (isset($arguments['file'])) {
            if (stripos($arguments['file'], 'EXT:') !== false) {
                $file = GeneralUtility::makeInstance(PackageManager::class)->resolvePackagePath($arguments['file']);
            } else {
                $file = Environment::getPublicPath() . '/' . $arguments['file'];
            }
        }

        if (null === $file) {
            $content = $renderChildrenClosure();
        } else {
            $content = is_file($file) ? file_get_contents($file) : null;
        }

        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addCssInlineBlock($arguments['name'], $content, $arguments['compress'], $arguments['forceOnTop'], true);
    }
}
