<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Utility;

use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Simple logger proxy
 *
 * @method static emergency(string|\Stringable $message, array $context = [])
 * @method static alert(string|\Stringable $message, array $context = [])
 * @method static critical(string|\Stringable $message, array $context = [])
 * @method static error(string|\Stringable $message, array $context = [])
 * @method static warning(string|\Stringable $message, array $context = [])
 * @method static notice(string|\Stringable $message, array $context = [])
 * @method static info(string|\Stringable $message, array $context = [])
 * @method static debug(string|\Stringable $message, array $context = [])
 * @method static log(string $level, string|\Stringable $message, array $context = [])
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class Log
{
    private static array $loggers = [];

    public static function __callStatic(string $name, array $arguments): void
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
            $class = $trace['class'] ?? null;

            if ($class && $class !== self::class) {
                $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger($class);
                $logger->{$name}(...$arguments);

                return;
            }
        }
    }
}
