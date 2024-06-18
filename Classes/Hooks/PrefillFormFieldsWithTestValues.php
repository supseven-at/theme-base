<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
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
    protected array $formSettings;
    protected array $testdata;

    /**
     * @param RenderableInterface $renderable
     * @throws InvalidConfigurationTypeException
     */
    public function initializeFormElement(RenderableInterface $renderable): void
    {

        $prefillWithTestdata = 0;

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $this->formSettings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'form');

        if (isset($this->formSettings['prefillWithTestdata'])) {
            $prefillWithTestdata = $this->formSettings['prefillWithTestdata'];
        }

        if (isset($this->formSettings['testdata'])) {
            $this->testdata = $this->formSettings['testdata'];
        }

        if ($GLOBALS['BE_USER'] && $GLOBALS['BE_USER']->isAdmin() && $prefillWithTestdata) {

            $field = $renderable->getIdentifier();

            if (isset($this->testdata[$field])) {
                $renderable->setDefaultValue($this->testdata[$field]);
            }
        }
    }
}
