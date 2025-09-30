<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Hooks;

use Supseven\ThemeBase\Service\DependencyValuesService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

/**
 * The hook prefills form fields with data from ts setting
 * plugin.tx_form.settings.testdata.<fieldidentifier>
 *
 * The Fields are only filled if the setting plugin.tx_form.settings.prefillWithTestdata is set to 1, an admin is logged in and defaultValue is not set.
 *
 * Example
 * plugin.tx_form {
 *      settings {
 *          prefillWithTestdata = 1
 *          testdata {
 *              salutation = Herr
 *              firstname = Max
 *              lastname = Mustermann
 *          }
 *      }
 * }
 */
class PrefillFormFieldsWithTestValues
{
    public function __construct(
        protected readonly DependencyValuesService $values,
        protected readonly ConfigurationManagerInterface $configurationManager,
    ) {
    }

    /**
     * @param RenderableInterface $renderable
     */
    public function initializeFormElement(RenderableInterface $renderable): void
    {
        $formSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'form');

        if (!empty($formSettings['prefillWithTestdata']) && !empty($formSettings['testdata'])) {
            if ($this->values->getBackendUser()?->isAdmin()) {
                $testdata = $formSettings['testdata'];
                $field = $renderable->getIdentifier();

                if (isset($testdata[$field])) {
                    $renderable->setDefaultValue($testdata[$field]);
                }
            }
        }
    }
}
