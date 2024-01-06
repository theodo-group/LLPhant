<?php

namespace LLPhant\Experimental\Agent\Render;

use LLPhant\Experimental\Agent\Task;

class WebOutputUtils implements OutputAgentInterface
{
    /** @var OutputWrapper[] */
    public array $messages = [];

    public string $filePath = '';

    public function __construct(public string $id)
    {
        $this->filePath = 'output-web'.$this->id.'.json';
    }

    /**
     * @throws \JsonException
     */
    public function render(string $message, bool $verbose): void
    {
        $messageWrapped = new OutputWrapper($message, 'info');
        $this->addMessageToFileSystem($messageWrapped);
    }

    /**
     * @throws \JsonException
     */
    public function renderTitle(string $title, string $message, bool $verbose): void
    {
        $messageWrapped = new OutputWrapper($message, 'title');
        $this->addMessageToFileSystem($messageWrapped);
    }

    /**
     * @throws \JsonException
     */
    public function renderTitleAndMessageGreen(string $title, string $message, bool $verbose): void
    {
        $messageWrapped = new OutputWrapper($message, 'title');
        $this->addMessageToFileSystem($messageWrapped);
    }

    /**
     * @throws \JsonException
     */
    public function renderTitleAndMessageOrange(string $title, string $message, bool $verbose): void
    {
        $messageWrapped = new OutputWrapper($message, 'title');
        $this->addMessageToFileSystem($messageWrapped);
    }

    /**
     * @throws \JsonException
     */
    public function printTasks(bool $verbose, array $tasks, ?Task $currentTask = null): void
    {
        $this->updateTaskToFileSystem($tasks);
    }

    /**
     * @throws \JsonException
     */
    public function renderResult(?string $result): void
    {
        $this->updateResultToFileSystem($result);
    }

    /**
     * @throws \JsonException
     */
    private function addMessageToFileSystem(OutputWrapper $messageWrapped): void
    {
        $dataObject = $this->readMessagesFromFile();
        $dataObject['messages'][] = $messageWrapped;
        $this->saveOutputToFileSystem($dataObject['tasks'], $dataObject['messages'], $dataObject['result']);
    }

    /**
     * @param  Task[]  $tasks
     *
     * @throws \JsonException
     */
    private function updateTaskToFileSystem(array $tasks): void
    {
        $dataObject = $this->readMessagesFromFile();
        $dataObject['tasks'] = $tasks;
        $this->saveOutputToFileSystem($dataObject['tasks'], $dataObject['messages'], $dataObject['result']);
    }

    /**
     * @throws \JsonException
     */
    private function updateResultToFileSystem(?string $result): void
    {
        $dataObject = $this->readMessagesFromFile();
        $dataObject['result'] = $result;
        $this->saveOutputToFileSystem($dataObject['tasks'], $dataObject['messages'], $dataObject['result']);
    }

    /**
     * @param  Task[]  $tasks
     * @param  OutputWrapper[]  $messages
     *
     * @throws \JsonException
     */
    private function saveOutputToFileSystem(array $tasks, array $messages, ?string $result): void
    {
        $arrayMessage = array_map(fn (OutputWrapper $outputWrapped): array => [
            'content' => $outputWrapped->content,
            'type' => $outputWrapped->type,
        ], $messages);

        $arrayTasks = array_map(fn (Task $task): array => [
            'name' => $task->name,
            'description' => $task->description ?? '',
            'result' => $task->result ?? null,
            'wasSuccessful' => $task->wasSuccessful ?? null,
        ], $tasks);

        $data = ['messages' => $arrayMessage, 'tasks' => $arrayTasks, 'result' => $result];
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($this->filePath, $jsonData);
    }

    /**
     * @return array{messages: OutputWrapper[], tasks: Task[], result: ?string}
     *
     * @throws \JsonException
     */
    public function readMessagesFromFile(): array
    {
        if (! file_exists($this->filePath)) {
            return ['messages' => [], 'tasks' => [], 'result' => null];
        }

        $jsonData = file_get_contents($this->filePath);
        if ($jsonData === false) {
            return ['messages' => [], 'tasks' => [], 'result' => null];
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($data)) {
            return ['messages' => [], 'tasks' => [], 'result' => null];
        }

        $messages = array_map(function (array $entry): OutputWrapper {
            $content = $entry['content'] ?? '';
            $type = $entry['type'] ?? null;

            return new OutputWrapper($content, $type);
        }, $data['messages']);

        $tasks = array_map(function (array $entry): Task {
            $result = $entry['result'] ?? '';
            $name = $entry['name'] ?? null;
            $description = $entry['description'] ?? null;
            $wasSuccessful = $entry['wasSuccessful'] ?? null;
            $task = new Task($name, $description, $result);
            $task->wasSuccessful = $wasSuccessful ?? false;

            return $task;
        }, $data['tasks']);

        $result = $data['result'] ?? null;

        return ['messages' => $messages, 'tasks' => $tasks, 'result' => $result];
    }
}
