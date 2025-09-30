<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Iterator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Creates chunks from an input Array/Traversable with option to allocate items to a fixed number of chunks
 *
 * thanks to Claus Due and the EXT:vhs where this has been copied from
 */
class ChunkViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('subject', 'mixed', 'The subject Traversable/Array instance to shift');
        $this->registerArgument('count', 'integer', 'Number of items/chunk or if fixed then number of chunks', true);
        $this->registerArgument('as', 'string', 'Template variable name to assign; if not specified the ViewHelper returns the variable instead.');
        $this->registerArgument(
            'fixed',
            'boolean',
            'If true, creates $count chunks instead of $count values per chunk',
            false,
            false
        );
        $this->registerArgument(
            'preserveKeys',
            'boolean',
            'If set to true, the original array keys will be preserved',
            false,
            false
        );
    }

    /**
     * @return array|mixed
     */
    public function render()
    {
        $count = (int)$this->arguments['count'];
        $fixed = (bool)($this->arguments['fixed'] ?? false);
        $preserveKeys = (bool)($this->arguments['preserveKeys'] ?? false);
        $subject = $this->arrayFromArrayOrTraversableOrCSVStatic(
            empty($this->arguments['as']) ? ($this->arguments['subject'] ?? $this->renderChildren()) : $this->arguments['subject'],
            $preserveKeys
        );
        $output = [];

        if ($count <= 0) {
            return $output;
        }

        if ($fixed) {
            $subjectSize = count($subject);

            if ($subjectSize > 0) {
                $chunkSize = (int)ceil($subjectSize / $count);

                $output = array_chunk($subject, $chunkSize, $preserveKeys);
            }
            // Fill the resulting array with empty items to get the desired element count
            $elementCount = count($output);

            if ($elementCount < $count) {
                $output += array_fill($elementCount, $count - $elementCount, null);
            }
        } else {
            $output = array_chunk($subject, $count, $preserveKeys);
        }

        return $this->renderChildrenWithVariableOrReturnInputStatic($output, $this->arguments['as']);
    }

    /**
     * @param mixed $candidate
     * @param bool $useKeys
     *
     * @return array
     */
    protected function arrayFromArrayOrTraversableOrCSVStatic(string|iterable $candidate, $useKeys = true): array
    {
        if ($candidate instanceof \Traversable) {
            return iterator_to_array($candidate, $useKeys);
        }

        if ($candidate instanceof QueryResultInterface) {
            return $candidate->toArray();
        }

        if (is_string($candidate)) {
            return GeneralUtility::trimExplode(',', $candidate, true);
        }

        if (is_array($candidate)) {
            return $candidate;
        }

        throw new Exception('Unsupported input type; cannot convert to array!', 1588049231);
    }

    /**
     * @param mixed $variable
     * @param string $as
     * @return mixed
     */
    protected function renderChildrenWithVariableOrReturnInputStatic(mixed $variable, string $as)
    {
        if (empty($as) === true) {
            return $variable;
        }

        $variables = [$as => $variable];

        return $this->renderChildrenWithVariablesStatic($variables);
    }

    /**
     * Renders tag content of ViewHelper and inserts variables
     * in $variables into $variableContainer while keeping backups
     * of each existing variable, restoring it after rendering.
     * Returns the output of the renderChildren() method on $viewHelper.
     *
     * @param array $variables
     * @return mixed
     */
    protected function renderChildrenWithVariablesStatic(array $variables)
    {
        $backups = $this->backupVariables($variables);
        $content = $this->renderChildren();
        $this->restoreVariables($variables, $backups);

        return $content;
    }

    /**
     * @param array $variables
     * @return array
     */
    private function backupVariables(array $variables): array
    {
        $backups = [];
        foreach ($variables as $variableName => $variableValue) {
            if ($this->templateVariableContainer->exists($variableName)) {
                $backups[$variableName] = $this->templateVariableContainer->get($variableName);
                $this->templateVariableContainer->remove($variableName);
            }
            $this->templateVariableContainer->add($variableName, $variableValue);
        }

        return $backups;
    }

    /**
     * @param array $variables
     * @param array $backups
     */
    private function restoreVariables(array $variables, array $backups): void
    {
        foreach ($variables as $variableName => $variableValue) {
            $this->templateVariableContainer->remove($variableName);

            if (isset($backups[$variableName]) === true) {
                $this->templateVariableContainer->add($variableName, $variableValue);
            }
        }
    }
}
