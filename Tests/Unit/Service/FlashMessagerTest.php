<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Supseven\ThemeBase\Service\FlashMessager;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class FlashMessagerTest extends TestCase
{
    public static function flashMessagesProvider(): iterable
    {
        return [
            ['notifyError', ContextualFeedbackSeverity::ERROR],
            ['notifyErr', ContextualFeedbackSeverity::ERROR],
            ['error', ContextualFeedbackSeverity::ERROR],
            ['err', ContextualFeedbackSeverity::ERROR],
            ['warn', ContextualFeedbackSeverity::WARNING],
            ['warning', ContextualFeedbackSeverity::WARNING],
            ['notifyWarn', ContextualFeedbackSeverity::WARNING],
            ['notifyWarning', ContextualFeedbackSeverity::WARNING],
            ['notifyNotice', ContextualFeedbackSeverity::NOTICE],
            ['notice', ContextualFeedbackSeverity::NOTICE],
            ['notifyInfo', ContextualFeedbackSeverity::INFO],
            ['info', ContextualFeedbackSeverity::INFO],
            ['ok', ContextualFeedbackSeverity::OK],
            ['success', ContextualFeedbackSeverity::OK],
            ['notifyOk', ContextualFeedbackSeverity::OK],
            ['notifySuccess', ContextualFeedbackSeverity::OK],
        ];
    }

    #[Test]
    #[DataProvider('flashMessagesProvider')]
    public function test_flash_messages(string $method, ContextualFeedbackSeverity $severity): void
    {
        $contentArg = 'LLL:EXT:content';
        $contentRes = 'Message Content';
        $titleArg = 'LLL:EXT:title';
        $titleRes = 'Title Content';
        $queueIdentifier = str_starts_with($method, 'notify') ? FlashMessageQueue::NOTIFICATION_QUEUE : FlashMessageQueue::FLASHMESSAGE_QUEUE;

        $languageService = $this->createMock(LanguageService::class);
        $languageService->expects($this->atLeastOnce())
            ->method('sL')
            ->willReturnCallback(fn ($str) => match ($str) {
                $contentArg => $contentRes,
                $titleArg   => $titleRes,
            });

        $expected = new FlashMessage($contentRes, $titleRes, $severity, true);

        $queue = $this->createMock(FlashMessageQueue::class);
        $queue->expects($this->once())->method('enqueue')->with($this->equalTo($expected));

        $service = $this->createMock(FlashMessageService::class);
        $service->expects($this->once())
            ->method('getMessageQueueByIdentifier')
            ->with($this->equalTo($queueIdentifier))
            ->willReturn($queue);

        $subject = new FlashMessager($service, $languageService);
        $subject->{$method}($contentArg, $titleArg);
    }
}
