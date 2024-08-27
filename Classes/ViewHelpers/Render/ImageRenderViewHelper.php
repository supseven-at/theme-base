<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\ViewHelpers\Render;

use Psr\Http\Message\RequestInterface;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Class ImageRenderViewHelper
 *
 * this viewhelper replaces the image render partial soon.
 *
 * EXAMPLE:
 *
 * <theme:render.imageRender settings="{settings}" image="{files.0}" />
 */
class ImageRenderViewHelper extends AbstractTagBasedViewHelper
{
    /** @var string $tagName */
    protected $tagName = 'picture';

    /** @var bool $escapeOutput */
    protected $escapeOutput = false;

    /** @var FileReference|null $image */
    private ?FileReference $image;

    /** @var array $breakpoints */
    private array $breakpoints;

    /** @var string $cropString */
    private string $cropString = '';

    /** @var array $contentObjectData */
    private array $contentObjectData;

    public function __construct(protected ImageService $imageService)
    {
        parent::__construct();
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->image = $this->arguments['image'];
        $this->breakpoints = $this->arguments['settings']['breakpoints'][$this->arguments['breakpoints']] ?? $this->arguments['settings']['breakpoints']['default'];
        $this->cropString = null !== $this->image ? $this->image->getProperty('crop') : '';
        $this->contentObjectData = $this->renderingContext->getRequest()->getAttribute('currentContentObject')->data;
    }

    /**
     * Initializes the arguments for the viewhelper.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();

        $this->registerArgument('settings', 'array', 'The Content Element Settings', true);
        $this->registerArgument('image', FileReference::class, 'Image Element', true);

        $this->registerArgument('breakpoints', 'string', 'The breakpoints array key in the settings', false, 'default');

        $this->registerArgument('pictureClass', 'string', 'CSS ClassNames for the Picture Element', false);
        $this->registerArgument('imgClass', 'string', 'CSS ClassNames for the Image Element', false, 'img-fluid');

        $this->registerArgument('lightboxClass', 'string', 'A CSS ClassName for the Lightbox', false, 'lightbox');
        $this->registerArgument('lightboxName', 'string', 'A Gallery for the Lightbox (data-attribute)', false, 'lightbox');

        $this->registerArgument('loading', 'string', 'Native lazy-loading for images property. Can be "lazy", "eager" or "auto"', false, 'lazy');
        $this->registerArgument('fileExtension', 'string', 'Custom file extension to use', false);
    }

    /**
     * Render the picture element with all its siblings.
     *
     * @return string The rendered image element.
     */
    public function render(): string
    {
        if (null === $this->image) {
            return '<!-- no image available -->';
        }

        foreach (array_reverse($this->breakpoints) as $breakpoint) {
            $imgSrc = [];

            $media = 'only screen and (' . ($breakpoint['media'] ?? 'min-width') . ': ' . $breakpoint['size'] . 'px)';
            $cropArea = $this->getCropping($breakpoint['cropVariant'] ?? 'default');
            $imgSrc[] = $this->processImage((int)$breakpoint['maxWidth'], $cropArea)['src'];

            if (!isset($this->arguments['settings']['breakpoints']['pixelDensities']['disabled'])) {
                foreach ($this->arguments['settings']['breakpoints']['pixelDensities'] as $value) {
                    $imgSrc[] = $this->processImage((int)$breakpoint['maxWidth'] * (int)$value['min-ratio'], $cropArea)['src'] . ' ' . (int)$value['min-ratio'] . 'x';
                }
            }

            $source[] = $this->getSourceElement($imgSrc, $media);
        }

        $source[] = $this->getImageElement();

        $this->tag->reset();
        $this->tag->setTagName($this->tagName);
        $this->tag->setContent(implode("\n", $source));

        if ($this->image->getProperty('link') || (int)$this->contentObjectData['image_zoom'] === 1) {
            return $this->getAnchorElement($this->tag->render());
        }

        return $this->tag->render();
    }

    /**
     * Process the image with the given width and crop area.
     *
     * @param int $width The desired width of the image.
     * @param Area $cropArea The crop area to apply on the image.
     * @return string The URI of the processed image.
     */
    public function processImage(int $width, Area $cropArea): array
    {
        if ((string)$this->arguments['fileExtension'] && !GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], (string)$this->arguments['fileExtension'])) {
            throw new Exception(
                $this->getExceptionMessage(
                    'The extension ' . $this->arguments['fileExtension'] . ' is not specified in $GLOBALS[\'TYPO3_CONF_VARS\'][\'GFX\'][\'imagefile_ext\']'
                    . ' as a valid image file extension and can not be processed.',
                ),
                1618989190
            );
        }

        $processingInstructions = [
            'width'         => $width,
            'crop'          => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($this->image),
            'fileExtension' => $this->arguments['fileExtension'] ?? null,
        ];

        $processedImage = $this->imageService->applyProcessingInstructions($this->image, array_filter($processingInstructions));

        return [
            'src'            => $this->imageService->getImageUri($processedImage),
            'processedImage' => $processedImage,
        ];
    }

    /**
     * Get the cropping area for the specified crop variant.
     *
     * @param string $cropVariant The name of the crop variant.
     * @return Area The cropping area for the specified crop variant.
     */
    public function getCropping(string $cropVariant): Area
    {
        $cropVariantCollection = CropVariantCollection::create((string)$this->cropString);

        return $cropVariantCollection->getCropArea($cropVariant);
    }

    /**
     * Generate the source element for the picture element.
     *
     * @param array $imgSrc The list of image source URLs.
     * @param string $media The media attribute value.
     * @return string The rendered source element.
     */
    public function getSourceElement(array $imgSrc, string $media): string
    {
        $this->tag->reset();
        $this->tag->setTagName('source');
        $this->tag->addAttributes([
            'srcset' => implode(', ', $imgSrc),
            'media'  => $media,
        ]);

        return $this->tag->render();
    }

    /**
     * Render the image element for the given file reference.
     *
     * @return string The rendered image element.
     */
    private function getImageElement(): string
    {
        $cropVariant = end($this->breakpoints)['cropVariant'] ?? 'default';
        $cropArea = $this->getCropping($cropVariant);
        $processedImage = $this->processImage(1280, $cropArea);
        $imgSrc = $processedImage['src'];

        $this->tag->reset();
        $this->tag->setTagName('img');
        $attributes = [
            'src'     => $imgSrc,
            'class'   => $this->arguments['imgClass'],
            'width'   => $processedImage['processedImage']->getProperty('width'),
            'height'  => $processedImage['processedImage']->getProperty('height'),
            'title'   => $this->image->getProperty('title') ?: null,
            'loading' => $this->arguments['loading'],
        ];

        // add alt attribute, or, if no alt is available, set image to aria-hidden to prevent
        // a11y issues.
        $alternative = $this->image->getProperty('alternative') ?: null;

        if (null !== $alternative) {
            $attributes['alt'] = $alternative;
        } else {
            $attributes['aria-hidden'] = true;
        }

        $this->tag->addAttributes(
            array_filter($attributes, fn ($value) => $value !== null)
        );

        return $this->tag->render();
    }

    /**
     * Get the anchor element for the image.
     *
     * @param string $content The content to be displayed within the anchor element.
     * @return string The rendered anchor element.
     */
    private function getAnchorElement(string $content): string
    {
        $this->tag->reset();
        $this->tag->setTagName('a');
        $attributes = [];

        /** @var ContentObjectRenderer $contentObjectRenderer */
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        if ($this->image->getProperty('link')) {
            $href = $contentObjectRenderer->typoLink_URL([
                'parameter' => $this->image->getProperty('link'),
            ]);
            $attributes['href'] = $href;
        }

        if ((int)$this->contentObjectData['image_zoom'] === 1) {
            $cropVariant = end($this->breakpoints)['cropVariant'] ?? 'default';
            $cropArea = $this->getCropping($cropVariant);
            $processedImage = $this->processImage(1280, $cropArea);
            $imgSrc = $processedImage['src'];

            $attributes = [
                'href'             => $imgSrc,
                'data-description' => $this->image->getProperty('description') ?: null,
                'data-gallery'     => $this->arguments['lightboxName'],
                'class'            => $this->arguments['lightboxClass'],
            ];

            $content = $this->renderChildren() . $content;
        }

        $this->tag->addAttributes(array_filter($attributes));

        $this->tag->setContent($content);

        return $this->tag->render();
    }

    /**
     * Get the exception message for rendering an image tag.
     *
     * @param string $detailedMessage The detailed error message.
     *
     * @return string The exception message.
     */
    public function getExceptionMessage(string $detailedMessage): string
    {
        /** @var RenderingContext $renderingContext */
        $renderingContext = $this->renderingContext;
        $request = $renderingContext->getRequest();

        if ($request instanceof RequestInterface) {
            $currentContentObject = $request->getAttribute('currentContentObject');

            if ($currentContentObject instanceof ContentObjectRenderer) {
                return sprintf('Unable to render image tag in "%s": %s', $currentContentObject->currentRecord, $detailedMessage);
            }
        }

        return "Unable to render image tag: {$detailedMessage}";
    }
}
