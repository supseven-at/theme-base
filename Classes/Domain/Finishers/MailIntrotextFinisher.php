<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Domain\Finishers;

/*
 * Add header and text for the recipient and the sender to be displayed in the email.
 * In the mail template header and text can be output with
 * {finisherVariableProvider.MailIntrotext.headerReceiver}
 * {finisherVariableProvider.MailIntrotext.textReceiver}
 * {finisherVariableProvider.MailIntrotext.headerSender}
 * {finisherVariableProvider.MailIntrotext.textSender}
 */

use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class MailIntrotextFinisher extends AbstractFinisher
{
    protected $defaultOptions = [
        'headerSender'   => '',
        'textSender'     => '',
        'headerReceiver' => '',
        'textReceiver'   => '',
    ];

    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     */
    protected function executeInternal(): void
    {
        foreach (array_keys($this->defaultOptions) as $option) {
            $value = $this->parseOption($option);
            $this->finisherContext->getFinisherVariableProvider()->add($this->shortFinisherIdentifier, $option, $value);
        }
    }
}
