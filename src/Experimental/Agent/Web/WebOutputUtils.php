<?php

namespace LLPhant\Experimental\Agent\Web;

use LLPhant\Experimental\Agent\OutputAgentInterface;
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
        echo json_encode($messageWrapped, JSON_PRETTY_PRINT);
        flush();
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
        echo json_encode($messageWrapped, JSON_PRETTY_PRINT);
        flush();
    }

    /**
     * @throws \JsonException
     */
    public function renderTitleAndMessageOrange(string $title, string $message, bool $verbose): void
    {
        $messageWrapped = new OutputWrapper($message, 'title');
        $this->addMessageToFileSystem($messageWrapped);
        echo json_encode($messageWrapped, JSON_PRETTY_PRINT);
        flush();
    }

    public function printTasks(bool $verbose, array $tasks, ?Task $currentTask = null): void
    {
        $this->updateTaskToFileSystem($tasks);
        echo json_encode($tasks, JSON_PRETTY_PRINT);
        flush();
    }

    /**
     * @throws \JsonException
     */
    private function addMessageToFileSystem(OutputWrapper $messageWrapped): void
    {
        $dataObject = $this->readMessagesFromFile();
        $dataObject['messages'][] = $messageWrapped;
        $this->saveOutputToFileSystem($dataObject['tasks'], $dataObject['messages']);
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
        $this->saveOutputToFileSystem($dataObject['tasks'], $dataObject['messages']);
    }

    /**
     * @param  Task[]  $tasks
     * @param  OutputWrapper[]  $messages
     *
     * @throws \JsonException
     */
    private function saveOutputToFileSystem(array $tasks, array $messages): void
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

        $data = ['messages' => $arrayMessage, 'tasks' => $arrayTasks];
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($this->filePath, $jsonData);
    }

    /**
     * @return array{messages: OutputWrapper[], tasks: Task[]}
     *
     * @throws \JsonException
     */
    public function readMessagesFromFile(): array
    {
        if (! file_exists($this->filePath)) {
            return ['messages' => [], 'tasks' => []];
        }

        $jsonData = file_get_contents($this->filePath);
        if ($jsonData === false) {
            return ['messages' => [], 'tasks' => []];
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($data)) {
            return ['messages' => [], 'tasks' => []];
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

        return ['messages' => $messages, 'tasks' => $tasks];
    }
}
