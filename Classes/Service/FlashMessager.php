<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper to create flash messages more easily
 *
 * Parameters `$content` and `$title` may be LLL: references
 *
 * Methods notify* add the message to the notification snack bar
 *
 * @method error(string $content, ?string $title = '') Create an error message
 * @method notifyError(string $content, ?string $title = '') Create an error notification
 * @method err(string $content, ?string $title = '') Create an error message
 * @method notifyErr(string $content, ?string $title = '') Create an error notification
 *
 * @method warn(string $content, ?string $title = '') Create a warning message
 * @method notifyWarn(string $content, ?string $title = '') Create a warning notification
 * @method warning(string $content, ?string $title = '') Create a warning message
 * @method notifyWarning(string $content, ?string $title = '') Create a warning notification
 *
 * @method notice(string $content, ?string $title = '') Create a notice message
 * @method notifyNotice(string $content, ?string $title = '') Create a notice notification
 *
 * @method info(string $content, ?string $title = '') Create an info message
 * @method notifyInfo(string $content, ?string $title = '') Create an info notification
 *
 * @method ok(string $content, ?string $title = '') Create an ok message
 * @method notifyOk(string $content, ?string $title = '') Create an ok notification
 * @method success(string $content, ?string $title = '') Create an ok message
 * @method notifySuccess(string $content, ?string $title = '') Create an ok notification
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class FlashMessager
{
    public function __construct(
        protected readonly FlashMessageService $flashMessageService,
        #[Autowire(service: 'typo3.lang')]
        protected readonly LanguageService $languageService,
    ) {
    }

    public function __call(string $name, array $arguments): void
    {
        $type = strtolower($name);
        $content = $arguments[0] ?? null;
        $title = $arguments[1] ?? null;

        if (empty($content) || !is_string($content)) {
            throw new \InvalidArgumentException('Parameter "$content" must be a non-empty string.');
        }

        if (str_starts_with($type, 'notify')) {
            $queue = $this->flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);
            $type = substr($type, 6);
        } else {
            $queue = $this->flashMessageService->getMessageQueueByIdentifier(FlashMessageQueue::FLASHMESSAGE_QUEUE);
        }

        $severity = match ($type) {
            'error', 'err' => ContextualFeedbackSeverity::ERROR,
            'warning', 'warn' => ContextualFeedbackSeverity::WARNING,
            'notice' => ContextualFeedbackSeverity::NOTICE,
            'info'   => ContextualFeedbackSeverity::INFO,
            'ok', 'success' => ContextualFeedbackSeverity::OK,
            default => throw new \InvalidArgumentException('Unknown flash message severity: ' . $type),
        };

        if (str_starts_with($content, 'LLL:')) {
            $content = $this->languageService->sL($content);
        }

        if (!is_string($title)) {
            $title = '';
        } elseif (str_starts_with($title, 'LLL:')) {
            $title = $this->languageService->sL($title);
        }

        $message = GeneralUtility::makeInstance(FlashMessage::class, $content, $title, $severity, true);

        $queue->enqueue($message);
    }
}
