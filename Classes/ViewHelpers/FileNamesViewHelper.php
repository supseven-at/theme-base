<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * fetches filenames within a given public folder
 */
class FileNamesViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('path', 'string', 'the path to the public folder', true);
        $this->registerArgument('extensionName', 'string', 'the extension name', true);
        $this->registerArgument('pattern', 'string', 'a possible filename pattern (e.g. *.svg)', false, '/*');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): array
    {
        $arr = []; // Images/Icons/*.svg
        $files = ExtensionManagementUtility::extPath($arguments['extensionName']) . 'Resources/Public/' . $arguments['path'] . $arguments['pattern'];
        foreach (glob($files) as $file) {
            $arr[] = pathinfo($file)['filename'];
        }

        return $arr;
    }
}
