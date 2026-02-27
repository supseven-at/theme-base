<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Attributes;

/**
 * Service tag to define a TypoScript cObj
 *
 * Alias of #[AutoconfigureTag('frontend.contentobject', ['identifier' => 'C_OBJ'])]
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AsContentObject
{
    public const TAG_NAME = 'frontend.contentobject';

    public function __construct(
        public string $name,
    ) {
    }
}
