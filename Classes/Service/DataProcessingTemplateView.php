<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Service;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;

/**
 * Extends the TemplateView with dataProcessing
 *
 * Applies dataProcessing to the variables, like the FLUID_TEMPLATE cObject
 * right before rendering.
 *
 * Processors can be set for the whole extension or specific
 * plugins:
 *
 * e.g.: setting the processor "ConcatDataProcessor"
 *
 * <code>
 * # For all plugins
 * plugin.tx_plugin {
 *     view.dataProcessing {
 *         # For all controllers and actions
 *         _default.10 = Supseven\ThemeBase\DataProcessing\ConcatDataProcessor
 *         # Only for controller "Records" and action "detail"
 *         Records.detail.10 = Supseven\ThemeBase\DataProcessing\ConcatDataProcessor
 *     }
 * }
 * # Only for plugin/CE "PluginData"
 * plugin.tx_plugin_data {
 *     view.dataProcessing {
 *          # For all controllers and actions of plugin
 *          _default.10 = Supseven\ThemeBase\DataProcessing\ConcatDataProcessor
 *          # Only for controller "Records" and action "detail" of plugin
 *          Records.detail.10 = Supseven\ThemeBase\DataProcessing\ConcatDataProcessor
 *     }
 * }
 * </code>
 *
 * Settings of `plugin.tx_plugin.view` and `plugin.tx_plugin_data.view` are merged. Then
 * the settings of `view.Controller.action` and `view._default` are merged as well.
 *
 * The ContentObjectRenderer instance passed to the data processors is
 * a clone of the Extbase-cObj and contains the Extbase request object
 *
 * @todo This needs to be moved to a Template-Adapter which needs a custom view-factory impl
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class DataProcessingTemplateView extends TemplateView implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function assign($key, $value)
    {
        $this->variables[$key] = $value;

        return $this;
    }

    public function assignMultiple(array $values)
    {
        foreach ($values as $key => $value) {
            $this->assign($key, $value);
        }

        return $this;
    }

    public function render($actionName = null)
    {
        foreach ($this->applyProcessors($this->variables) as $key => $value) {
            $this->baseRenderingContext->getVariableProvider()->add($key, $value);
        }

        return parent::render($actionName);
    }

    protected function applyProcessors(array $data): array
    {
        if (!$data) {
            $this->logger->info('No variables provided, skipping data processing');

            return [];
        }

        $ctx = $this->getRenderingContext();

        if (!$ctx instanceof RenderingContext) {
            $this->logger->warning('No extbase rendering context, skipping data processing');

            return $data;
        }

        $request = $ctx->getRequest();

        if (!$request instanceof RequestInterface) {
            $this->logger->warning('No extbase MVC request object, skipping data processing');

            return $data;
        }

        $extbase = $request->getAttribute('extbase');

        if (!$extbase instanceof ExtbaseRequestParameters) {
            $this->logger->warning('No extbase request parameters, skipping data processing');

            return $data;
        }

        $typoscript = $request->getAttribute('frontend.typoscript');

        if (!$typoscript instanceof FrontendTypoScript) {
            $this->logger->warning('No frontend typoscript, skipping data processing');

            return $data;
        }

        $extKey = 'tx_' . strtolower($extbase->getControllerExtensionName()) . '.';
        $pluginKey = 'tx_' . strtolower($extbase->getControllerExtensionName()) . '_' . strtolower($request->getPluginName()) . '.';
        $fullTyposcript = $typoscript->getSetupArray()['plugin.'] ?? [];

        $pluginSettings = [];

        ArrayUtility::mergeRecursiveWithOverrule($pluginSettings, $fullTyposcript[$extKey] ?? []);
        ArrayUtility::mergeRecursiveWithOverrule($pluginSettings, $fullTyposcript[$pluginKey] ?? []);

        $processorSettings = $pluginSettings['view.']['dataProcessing.'] ?? [];

        if (empty($processorSettings)) {
            $this->logger->info('No data processing in view TS, skipping data processing');

            return $data;
        }

        $processingConfig = [];

        ArrayUtility::mergeRecursiveWithOverrule($processingConfig, $processorSettings[$extbase->getControllerName() . '.'][$extbase->getControllerActionName() . '.'] ?? []);
        ArrayUtility::mergeRecursiveWithOverrule($processingConfig, $processorSettings['_default.'] ?? []);

        if (!$processingConfig) {
            $this->logger->info('No data processing settings for this plugin/controller/action, skipping data processing');

            return $data;
        }

        $this->logger->debug('Applying data processing to view variables', compact('processingConfig'));

        $cObj = clone $request->getAttribute('currentContentObject');
        $cObj->setRequest($request);

        $processor = GeneralUtility::makeInstance(ContentDataProcessor::class);

        return $processor->process($cObj, ['dataProcessing.' => $processingConfig], $data);
    }
}
