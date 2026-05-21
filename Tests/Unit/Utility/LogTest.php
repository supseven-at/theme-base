<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Tests\Unit\Utility;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Supseven\ThemeBase\Utility\Log;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
#[CoversClass(Log::class)]
class LogTest extends TestCase
{
    public static function logProvider(): iterable
    {
        return [
            [LogLevel::EMERGENCY],
            [LogLevel::ALERT],
            [LogLevel::CRITICAL],
            [LogLevel::ERROR],
            [LogLevel::WARNING],
            [LogLevel::NOTICE],
            [LogLevel::INFO],
            [LogLevel::DEBUG],
        ];
    }

    #[Test]
    #[DataProvider('logProvider')]
    public function magicMethods(string $expectedMethod): void
    {
        $message = 'Important message';
        $logger = $this->createMock(Logger::class);

        $levels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ];

        foreach ($levels as $level) {
            if ($level === $expectedMethod) {
                $logger->expects($this->once())->method($level)->with($this->equalTo($message));
            } else {
                $logger->expects($this->never())->method($level);
            }
        }

        $logManager = $this->createMock(LogManager::class);
        $logManager->expects($this->atLeastOnce())
            ->method('getLogger')
            ->with('Supseven\\ThemeBase\\Tests\\Unit\\Utility\\LogTest')
            ->willReturn($logger);

        GeneralUtility::setSingletonInstance(LogManager::class, $logManager);

        Log::{$expectedMethod}($message);
    }

    #[Test]
    public function directLogCall(): void
    {
        $severity = LogLevel::ERROR;
        $message = 'LogMessage';

        $logger = $this->createMock(Logger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with($this->equalTo($severity), $this->equalTo($message));

        $logManager = $this->createMock(LogManager::class);
        $logManager->expects($this->atLeastOnce())
            ->method('getLogger')
            ->with('Supseven\\ThemeBase\\Tests\\Unit\\Utility\\LogTest')
            ->willReturn($logger);

        GeneralUtility::setSingletonInstance(LogManager::class, $logManager);

        Log::log($severity, $message);
    }
}
