<?php

declare(strict_types=1);

namespace Tests\Integration\Chat;

class NotificationExample
{
    /** @var int */
    public $nrOfCalls = 0;

    /**
     * Send Confirmation to the Slack
     */
    public function sendNotificationToSlack(): void
    {
        $this->nrOfCalls++;
        echo 'Sending Notification....';
    }
}
