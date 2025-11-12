<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * fetches filenames within a given public folder
 */
class FileNamesViewHelper extends AbstractViewHelper
{
    public function __construct(
        protected readonly \TYPO3\CMS\Core\Package\PackageManager $packageManager,
    ) {
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('path', 'string', 'the path to the public folder', true);
        $this->registerArgument('extensionName', 'string', 'the extension name', true);
        $this->registerArgument('pattern', 'string', 'a possible filename pattern (e.g. *.svg)', false, '/*');
    }

    /**
     * @return array
     */
    public function render(): array
    {
        $arr = []; // Images/Icons/*.svg
        $extPath = $this->packageManager->getPackage($this->arguments['extensionName'])->getPackagePath();
        $files = $extPath . $this->arguments['path'] . $this->arguments['pattern'];

        foreach (glob($files) as $file) {
            $arr[] = pathinfo($file)['filename'];
        }

        return $arr;
    }
}
