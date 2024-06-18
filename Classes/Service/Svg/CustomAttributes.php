<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Service\Svg;

use enshrined\svgSanitize\data\AllowedAttributes;
use enshrined\svgSanitize\data\AttributeInterface;

/**
 * Represents a set of custom attributes that can be used in the Svg Sanitizer used in the InlineSvgViewHelper
 *
 * EXAMPLE:
 *
 * <theme:render.inlineSvg
 *      source="EXT:theme_project/Resources/Public/Images/Svg/Loading.svg"
 *      custom-tags="{ 0: 'animate' }"
 *      custom-attributes="{ 0: 'calcMode' }" />
 */
class CustomAttributes implements AttributeInterface
{
    private static array $allowedAttributes = [];

    public function __construct(array $attributes)
    {
        self::$allowedAttributes = array_unique(array_merge(AllowedAttributes::getAttributes(), $attributes));
    }

    public static function getAttributes(): array
    {
        return self::$allowedAttributes;
    }
}
