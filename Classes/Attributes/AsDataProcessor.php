<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Attributes;

/**
 * Service tag to define the short name of a data processor
 *
 * Alias of #[AutoconfigureTag('data.processor', ['identifier' => 'short-name'])]
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AsDataProcessor
{
    public const TAG_NAME = 'data.processor';

    public function __construct(
        public string $shortName,
    ) {
    }
}
