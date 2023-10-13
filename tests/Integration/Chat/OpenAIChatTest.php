<?php

declare(strict_types=1);

namespace Tests\Integration\Chat;

use LLPhant\Chat\Function\FunctionInfo;
use LLPhant\Chat\Function\Parameter;
use LLPhant\Chat\OpenAIChat;
use LLPhant\OpenAIConfig;
use Tests\Integration\Chat\MailerExample;

it('can generate some stuff', function () {
    $chat = new OpenAIChat();
    $response = $chat->generateText('what is one + one ?');
    expect($response)->toBeString();
});

it('can generate some stuff with a system prompt', function () {
    $chat = new OpenAIChat();
    $chat->setSystemMessage('Whatever we ask you, you MUST answer "ok"');
    $response = $chat->generateText('what is one + one ?');
    expect(strtolower($response))->toBe('ok');
});

it('can load any existing model', function () {
    $config = new OpenAIConfig();
    $config->model = 'gpt-3.5-turbo-16k';
    $chat = new OpenAIChat($config);
    $response = $chat->generateText('one + one ?');
    expect($response)->toBeString();
});

it('can call a function', function () {
    $chat = new OpenAIChat();

    $subject = new Parameter('subject', 'string', 'the subject of the mail');
    $body = new Parameter('body', 'string', 'the body of the mail');
    $email = new Parameter('email', 'string', 'the email address');

    $mockMailerExample = Mockery::mock(MailerExample::class);
    $mockMailerExample->shouldReceive('sendMail')->once()->andReturn(null);

    $function = new FunctionInfo(
        'sendMail',
        $mockMailerExample,
        'send a mail',
        [$subject, $body, $email]
    );

    $chat->addFunction($function);
    $chat->setSystemMessage('You are an AI that deliver information using the email system. When you have enough information to answer the question of the user you send a mail');
    $chat->generateText('Who is Marie Curie in one line? My email is student@foo.com');
});

it('can call a function while streaming', function () {
    $chat = new OpenAIChat();

    $subject = new Parameter('subject', 'string', 'the subject of the mail');
    $body = new Parameter('body', 'string', 'the body of the mail');
    $email = new Parameter('email', 'string', 'the email adress');

    $mockMailerExample = Mockery::mock(MailerExample::class);
    $mockMailerExample->shouldReceive('sendMail')->once()->andReturn(null);

    $function = new FunctionInfo(
        'sendMail',
        $mockMailerExample,
        'send a mail',
        [$subject, $body, $email]
    );

    $chat->addFunction($function);
    $chat->setSystemMessage('You are an AI that deliver information using the email system. When you have enough information to answer the question of the user you send a mail');
    $chat->generateStreamOfText('Who is Marie Curie in one line? My email is student@foo.com');
});
