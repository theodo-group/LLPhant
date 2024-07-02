<?php

namespace Tests\Unit\Experimental\Agent;

use LLPhant\Experimental\Agent\TaskManager;

it('does not crash with wrong parameter array', function () {
    $taksManager = new TaskManager();

    $taksManager->addTasks([['foo' => 'bar']]);

    expect($taksManager->tasks)->toBeEmpty();
});

it('works with correct parameter array', function () {
    $taksManager = new TaskManager();

    $taksManager->addTasks([['name' => 'foo', 'description' => 'bar']]);

    expect($taksManager->tasks)->toHaveCount(1);
    $task = $taksManager->tasks[0];
    expect($task->name)->toBe('foo')->and($task->description)->toBe('bar');
});
