<?php

declare(strict_types=1);

namespace Tests\Integration\Chat;

class MailerExample
{
    /**
     * This function send an email
     */
    public function sendMail(string $subject, string $body, string $email): void
    {
        echo 'The email has been sent to '.$email.' with the subject '.$subject.' and the body '.$body.'.';
    }
}
