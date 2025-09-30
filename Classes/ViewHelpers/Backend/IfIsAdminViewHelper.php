<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Backend;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * IfIsAdminViewHelper class extends AbstractConditionViewHelper and is used to determine if the user is an admin.
 *
 * Used in BE-Previews to show additional information only for Admins
 *
 * Example:
 *
 * <theme:backend.ifIsAdmin>
 * Your Code here.
 * </theme:backend.ifIsAdmin>
 */
class IfIsAdminViewHelper extends AbstractConditionViewHelper
{
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext)
    {
        return $GLOBALS['BE_USER']->isAdmin();
    }
}
