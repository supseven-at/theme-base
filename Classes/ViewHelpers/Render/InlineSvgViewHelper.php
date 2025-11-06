<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Render;

use enshrined\svgSanitize\Sanitizer;
use Supseven\ThemeBase\Service\Svg\CustomAttributes;
use Supseven\ThemeBase\Service\Svg\CustomTags;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class InlineSvgViewHelper
 *
 * Return the adjusted content of a svg file
 *
 * = Examples
 * <code title="basic inline svg">
 *  <theme:render.inlineSvg source="{f:uri.resource(path: 'Icons/FileIcons/{file.extension}.svg', extensionName: 'theme')}" />
 * </code>
 * <output>
 * <svg><contentOfTheSvgFile</svg>
 * <output>
 *
 * = Notes
 *
 * This ViewHelper renders Inline Svg from a given SVG File. To make the SVG accesssible, you can add an ID and a TITLE to the
 * produced inline svg. the original title will alway be removed. if you set a id but no title, the viewhelper throws an exception
 * and will not be rendered.
 *
 * it adds styles (from the svg or via argument) to the head with the asset collector to prevent CSP violations
 */
class InlineSvgViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Arguments initialization
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('source', 'string', 'Source of svg resource', true);
        $this->registerArgument('class', 'string', 'Specifies an alternate class for the svg', false);
        $this->registerArgument('width', 'string', 'Specifies a width for the svg', false);
        $this->registerArgument('height', 'string', 'Specifies a height for the svg', false);
        $this->registerArgument('aria-hidden', 'bool', 'Activate aria-hidden=true attribute', false);
        $this->registerArgument('insert-style', 'string', 'Add CSS Styles', false);
        $this->registerArgument('id', 'string', 'Add UNIQUE Id', false);
        $this->registerArgument('uniqueId', 'int', 'adds a unique id, if a id is set - mostly from a file upload', false);
        $this->registerArgument('title', 'string', 'Add descriptive title for the SVG', false);
        $this->registerArgument('fill', 'string', 'add a fill color', false);
        $this->registerArgument('remove-styles', 'bool', 'remove all style tags', false);
        $this->registerArgument('move-styles', 'bool', 'move style from styletags to asset collector', false);
        $this->registerArgument('custom-tags', 'array', 'add allowed svg tags to the sanitizer', false);
        $this->registerArgument('custom-attributes', 'array', 'add allowed svg tag attributes to the sanitizer', false);
        $this->registerArgument('additionalContent', 'string', 'Add additional Text Content to the SVG', false);
    }

    /**
     * Output different objects
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $file = self::getFilePath($arguments['source']);

        if (empty($arguments['source']) || !file_exists($file)) {
            throw new FileDoesNotExistException('File not found', 1725027772);
        }

        try {
            return self::getInlineSvg($file, $arguments);
        } catch (\Exception $e) {
            if ($e->getCode() === 1614863553) {
                return '<!-- ' . $e->getMessage() . ' -->';
            }

            return '<!-- SVG generation produced error! -->';
        }
    }

    /**
     * @param string $source
     *
     * @return string
     * @throws \TYPO3\CMS\Core\Package\Exception\UnknownPackageException
     * @throws \TYPO3\CMS\Core\Package\Exception\UnknownPackagePathException
     */
    public static function getFilePath(string $source): string
    {
        if (stripos($source, 'EXT:') !== false) {
            return GeneralUtility::makeInstance(PackageManager::class)->resolvePackagePath($source);
        }

        return Environment::getPublicPath() . '/' . $source;
    }

    /**
     * sanitizes a possible id string to a correct usable id string
     *
     * @param string $id
     * @return string
     */
    public static function sanitizeId(string $id): string
    {
        // Replace any character that is not a letter, digit, colon, hyphen, or period with an underscore
        $svgId = (string)preg_replace('/[^a-zA-Z0-9_:.-]/', '_', $id);

        // If the ID starts with a digit, prepend it with an 'x' and convert the rest of the string
        return htmlspecialchars((string)preg_replace('/^[^a-zA-Z]/', 'x', $svgId), ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param string $source
     * @param array $arguments
     * @return string
     */
    protected static function getInlineSvg(string $source, array $arguments)
    {
        $svgContent = file_get_contents($source);
        $svgContent = preg_replace('/<script[\\s\\S]*?>[\\s\\S]*?<\\/script>/i', '', $svgContent);
        $svgContent = preg_replace('/<title[\\s\\S]*?>[\\s\\S]*?<\\/title>/i', '', $svgContent);

        // Disables the functionality to allow external entities to be loaded when parsing the XML, must be kept
        $svgElement = simplexml_load_string($svgContent);
        // remove xml version tag
        $domXml = dom_import_simplexml($svgElement);

        // add role attribute for a11y
        $domXml->setAttribute('role', 'img');

        if ($arguments['remove-styles'] === true) {
            self::removeStyleTags($domXml, $source, $arguments);
        }

        // if a title should be used, create an extra node
        if (trim((string)($arguments['title'] ?? '')) !== '') {
            $title = $domXml->ownerDocument->createElement('title', $arguments['title']);
        }

        // change all fill attributes to the value given from the viewhelper
        if ($arguments['fill']) {
            $domXml->setAttribute('fill', $arguments['fill']);

            foreach ($domXml->childNodes as $node) {
                if ($node instanceof \DOMElement) {
                    $node->setAttribute('fill', $arguments['fill']);

                    foreach ($node->childNodes as $n) {
                        if ($n instanceof \DOMElement) {
                            $n->setAttribute('fill', $arguments['fill']);
                        }
                    }
                }
            }
        }

        // if there is an id, it means, this svg should be accessible. therefore a title MUST be set
        // if not, it throws a exception and will not be rendered.
        // if no id is set, the SVG is automatically marked as aria-hidden for a11y reasons
        if (!empty($arguments['id'])) {
            $domXml->setAttribute('aria-labelledby', self::sanitizeId($arguments['id']));

            if (isset($title)) {
                $title->setAttribute('id', self::sanitizeId($arguments['id']));
            } else {
                throw new Exception('This SVG is not accessible, if there is no Title attribute available', 1614863553);
            }
        } else {
            $domXml->setAttribute('aria-hidden', 'true');
        }

        // replace a existing (possibly duplicate) id with a unique id.
        // this is mostly required, if a svg is uploaded by an editor
        if (!is_null($arguments['uniqueId']) && $domXml->getAttribute('id') !== '') {
            $domXml->setAttribute('id', 'svg-' . $arguments['uniqueId']);
            $domXml->removeAttribute('data-name');
        }

        // at this point, the title will be appended to the svg object
        if (isset($title)) {
            $domXml->appendChild($title);
        }

        // if you have inline styles, like css variables, you can use this argument to add styles directly to the
        // svg.
        if (isset($arguments['insert-style'])) {
            self::addStyles($arguments['insert-style'], time() . pathinfo($source)['filename']);
        }

        if (isset($arguments['class'])) {
            $domXml->setAttribute('class', $arguments['class']);
        }

        if (isset($arguments['width'])) {
            if ($arguments['width'] > 0) {
                $domXml->setAttribute('width', (string)$arguments['width']);
            } else {
                $domXml->removeAttribute('width');
            }
        }

        if (isset($arguments['height'])) {
            if ($arguments['height'] > 0) {
                $domXml->setAttribute('height', (string)$arguments['height']);
            } else {
                $domXml->removeAttribute('height');
            }
        }

        if (isset($arguments['additionalContent']) && is_array($arguments['additionalContent'])) {
            $domXl = self::addAdditionalContent($domXml, $arguments['additionalContent']);
        }

        $finalSvgString = $domXml->ownerDocument->saveXML($domXml->ownerDocument->documentElement);

        return self::sanitizeContent($finalSvgString, $arguments);
    }

    private static function addAdditionalContent(\DOMElement $domXml, array $config): \DOMElement
    {
        $text = $config['text'] ?? '';
        $textAreaWidth = (float)($config['width'] ?? 0);
        $textPadding = (float)($config['padding'] ?? 20);
        $showDivider = (bool)($config['divider'] ?? true);
        $centerCalc = (float)($config['centerCalc'] ?? 1.5);
        $fontSize = (int)($config['fontSize'] ?? 0);
        $cssClass = $config['cssClass'] ?? 'wx-logo-tagLine';

        if (empty($text)) {
            return $domXml;
        }

        $additionalContent = GeneralUtility::trimExplode(PHP_EOL, $text, true);

        $viewBox = $domXml->getAttribute('viewBox');

        if (!$viewBox) {
            return $domXml;
        }

        $viewBoxValues = explode(' ', $viewBox);
        $startX = (float)($viewBoxValues[0] ?? 0);
        $startY = (float)($viewBoxValues[1] ?? 0);
        $width = (float)($viewBoxValues[2] ?? 100);
        $height = (float)($viewBoxValues[3] ?? 100);

        if ($textAreaWidth <= 0 && $fontSize > 0) {
            $textAreaWidth = self::calculateAdditionalContentWidth($additionalContent, $fontSize, $textPadding);
        }

        $newViewBox = sprintf('%s %s %s %s', $startX, $startY, $width + $textAreaWidth, $height);
        $domXml->setAttribute('viewBox', $newViewBox);

        if ($showDivider) {
            $dividerPadding = $textPadding / 2;
            $dividerX = $startX + $width + $dividerPadding;
            $dividerY1 = $startY;
            $dividerY2 = $startY + ($height);

            $lineElement = $domXml->ownerDocument->createElement('line');
            $lineElement->setAttribute('x1', (string)$dividerX);
            $lineElement->setAttribute('y1', (string)$dividerY1);
            $lineElement->setAttribute('x2', (string)$dividerX);
            $lineElement->setAttribute('y2', (string)$dividerY2);
            $lineElement->setAttribute('stroke', 'currentColor');
            $lineElement->setAttribute('stroke-width', '2');
            $lineElement->setAttribute('class', 'svg-divider');
            $domXml->appendChild($lineElement);
        }

        $textStartX = $startX + $width + $textPadding;
        $textStartY = $startY + ($height / $centerCalc);

        foreach ($additionalContent as $line) {
            $textElement = $domXml->ownerDocument->createElement('text', htmlspecialchars($line, ENT_XML1, 'UTF-8'));
            $textElement->setAttribute('x', (string)$textStartX);
            $textElement->setAttribute('y', (string)$textStartY);
            $textElement->setAttribute('fill', 'currentColor');
            $textElement->setAttribute('dominant-baseline', 'middle');
            $textElement->setAttribute('text-anchor', 'start');
            $textElement->setAttribute('class', $cssClass);
            if ($fontSize > 0) {
                $textElement->setAttribute('font-size', (string)$fontSize);
            }
            $domXml->appendChild($textElement);
        }

        return $domXml;
    }

    private static function calculateAdditionalContentWidth(array $lines, int $fontSize, float $padding): float
    {
        $longestLine = '';
        $maxLength = 0;

        foreach ($lines as $line) {
            $lineLength = mb_strlen($line);

            if ($lineLength > $maxLength) {
                $maxLength = $lineLength;
                $longestLine = $line;
            }
        }

        $avgCharWidth = $fontSize * 0.6;
        $estimatedWidth = $maxLength * $avgCharWidth;
        $totalWidth = $estimatedWidth + ($padding * 2);

        return $totalWidth;
    }

    /**
     * copied from core to to missing possibility to remove xml tag
     *
     * @throws \BadFunctionCallException
     */
    private static function sanitizeContent(string $svg, array $arguments): string
    {
        $previousXmlErrorHandling = libxml_use_internal_errors(true);
        $sanitizer = new Sanitizer();

        if ($arguments['custom-tags']) {
            $sanitizer->setAllowedTags(new CustomTags($arguments['custom-tags']));
        }

        if ($arguments['custom-attributes']) {
            $sanitizer->setAllowedAttrs(new CustomAttributes($arguments['custom-attributes']));
        }

        $sanitizer->removeRemoteReferences(true);
        $sanitizer->removeXMLTag(true);
        $sanitizedString = $sanitizer->sanitize($svg) ?: '';
        libxml_clear_errors();
        libxml_use_internal_errors($previousXmlErrorHandling);

        return $sanitizedString;
    }

    /**
     * Removes all `style` tags from the given `DOMElement` object.
     * If the `$arguments['move-styles']` option is set to `true`, the removed styles will be added using the `addStyles` method.
     *
     * @param \DOMElement $svg The SVG element
     * @param string $source The source path of the SVG file
     * @param array $arguments The arguments array
     * @throws \DOMException If an error occurred while removing the `style` tags
     */
    private static function removeStyleTags(\DOMElement $svg, string $source, array $arguments): void
    {
        $styles = '';
        $styleTags = $svg->getElementsByTagName('style');

        for ($i = 0; $i < $styleTags->length; $i++) {
            $styleTag = $styleTags->item($i);
            $styles .= $styleTag->nodeValue;
            $styleTag->parentNode->removeChild($styleTag);
        }

        if ($arguments['move-styles']) {
            self::addStyles($styles, pathinfo($source)['filename']);
        }
    }

    /**
     * Add styles to the asset collector
     *
     * @param string $styles The styles to be added
     * @param string $name The name of the stylesheet
     * @throws \BadFunctionCallException
     */
    private static function addStyles(string $styles, string $name): void
    {
        /** @var AssetCollector $assetCollector */
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        $assetCollector->addInlineStyleSheet($name, $styles, [], [
            'priority' => true,
            'useNonce' => true,
        ]);
    }
}
