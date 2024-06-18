<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Backend;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * IfIsAdminViewHelper class extends AbstractConditionViewHelper and is used to determine if the user is an admin.
 *
 * Used in BE-Previews to show additional informations only for Admins
 *
 * Example:
 *
 * <theme:backend.ifIsAdmin>
 * Your Code here.
 * </theme:backend.ifIsAdmin>
 */
class IfIsAdminViewHelper extends AbstractConditionViewHelper
{
    /**
     * This method decides if the condition is TRUE or FALSE. It can be overridden in extending viewhelpers to adjust functionality.
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexibility in overriding this method.
     * @return bool
     */
    protected static function evaluateCondition($arguments = null)
    {
        return $GLOBALS['BE_USER']->user['admin'] === 1;
    }
}
