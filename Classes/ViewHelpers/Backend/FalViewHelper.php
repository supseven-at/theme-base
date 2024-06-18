<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Backend;

use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class FalViewHelper
 *
 * = Example =
 *
 * <code title="register namespace in fluid first">
 * xmlns:theme="http://typo3.org/ns/Supseven/ThemeBase/ViewHelpers"
 * </code>
 *
 * <code title="default notation">
 * <theme:backend.fal table="pages" field="image" id="{row.uid}" as="references">
 * <f:if condition="{references}">
 *  <f:then>
 *    <f:media file="{references.0}" class="foobar" title="{references.0.propertiesOfFileReference.title}"/>
 *  </f:then>
 *  <f:else>
 *    <img class="dummy" src="https://dummyimage.com/600x600/444/fff" alt="">
 *  </f:else>
 * </f:if>
 * </theme:backend.fal>
 * </code>
 */
class FalViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('table', 'string', '', true);
        $this->registerArgument('field', 'string', '', true);
        $this->registerArgument('id', 'integer', '', true);
        $this->registerArgument('as', 'string', '', false, 'references');
        $this->registerArgument('combine', 'bool', 'combine entries from more than one column into one object/array. comma separate the fields, when set combine to true', false, defaultValue: false);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): mixed
    {
        $fields = [ $arguments['field'] ];

        if ($arguments['combine']) {
            $fields = GeneralUtility::trimExplode(',', $arguments['field']);
        }

        foreach ($fields as $field) {
            $files = GeneralUtility::makeInstance(FileRepository::class)->findByRelation(
                $arguments['table'],
                $field,
                $arguments['id']
            );

            if ($arguments['combine']) {
                $arr[] = $files;
            }
        }

        if (isset($arr)) {
            $files = array_merge(...$arr);
        }

        $vars = $renderingContext->getVariableProvider();
        $as = $arguments['as'];

        $vars->add($as, $files ?? []);
        $content = $renderChildrenClosure();
        $vars->remove($as);

        return $content;
    }
}
