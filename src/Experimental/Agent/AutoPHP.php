<?php

namespace LLPhant\Experimental\Agent;

use LLPhant\Chat\Enums\OpenAIChatModel;
use LLPhant\Chat\FunctionInfo\FunctionBuilder;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Experimental\Agent\Render\CLIOutputUtils;
use LLPhant\Experimental\Agent\Render\OutputAgentInterface;
use LLPhant\OpenAIConfig;

class AutoPHP
{
    public TaskManager $taskManager;

    public CreationTaskAgent $creationTaskAgent;

    public PrioritizationTaskAgent $prioritizationTaskAgent;

    public string $defaultModelName;

    /**
     * @param  FunctionInfo[]  $tools
     */
    public function __construct(
        public string $objective,
        /* @var FunctionInfo[] */
        public array $tools,
        public bool $verbose = false,
        public OutputAgentInterface $outputAgent = new CLIOutputUtils(),
    ) {
        $this->taskManager = new TaskManager();
        $this->creationTaskAgent = new CreationTaskAgent($this->taskManager, new OpenAIChat(), $tools, $verbose,
            $this->outputAgent);
        $this->prioritizationTaskAgent = new PrioritizationTaskAgent($this->taskManager, new OpenAIChat(), $verbose,
            $this->outputAgent);
        $this->defaultModelName = OpenAIChatModel::Gpt4Turbo->value;
    }

    public function run(int $maxIteration = 10): string
    {
        $this->outputAgent->renderTitle('🐘 AutoPHP 🐘', '🎯 Objective: '.$this->objective, $this->verbose);
        $this->creationTaskAgent->createTasks($this->objective, $this->tools);
        $this->outputAgent->printTasks($this->verbose, $this->taskManager->tasks);
        $currentTask = $this->prioritizationTaskAgent->prioritizeTask($this->objective);
        $iteration = 1;
        while ($currentTask instanceof Task && $maxIteration >= $iteration) {
            $this->outputAgent->render('Iteration '.$iteration, false);
            $this->outputAgent->printTasks($this->verbose, $this->taskManager->tasks, $currentTask);

            // TODO: add a mechanism to retrieve short-term / long-term memory
            $previousCompletedTask = $this->taskManager->getAchievedTasksNameAndResult();
            $context = "Previous tasks status: {$previousCompletedTask}";
            $this->checkForCancellation();

            // TODO: add a mechanism to get the best tool for a given Task
            $executionAgent = new ExecutionTaskAgent($this->tools, new OpenAIChat(), $this->verbose);
            $currentTask->result = $executionAgent->run($this->objective, $currentTask, $context);

            $this->outputAgent->printTasks($this->verbose, $this->taskManager->tasks);
            if ($finalResult = $this->getObjectiveResult()) {
                $this->outputAgent->renderResult($finalResult);

                return $finalResult;
            }
            $this->checkForCancellation();

            if (count($this->taskManager->getUnachievedTasks()) <= 0) {
                $this->creationTaskAgent->createTasks($this->objective, $this->tools);
            }

            $currentTask = $this->prioritizationTaskAgent->prioritizeTask($this->objective);
            $this->checkForCancellation();
            $iteration++;
        }

        return "failed to achieve objective in {$iteration} iterations";
    }

    private function getObjectiveResult(): ?string
    {
        $config = new OpenAIConfig();
        $config->model = $this->defaultModelName;
        $model = new OpenAIChat($config);
        $autoPHPInternalTool = new AutoPHPInternalTool();
        $enoughDataToFinishFunction = FunctionBuilder::buildFunctionInfo($autoPHPInternalTool, 'objectiveStatus');
        $model->setTools([$enoughDataToFinishFunction]);
        $model->requiredFunction = $enoughDataToFinishFunction;

        $achievedTasks = $this->taskManager->getAchievedTasksNameAndResult();
        $unachievedTasks = $this->taskManager->getUnachievedTasksNameAndResult();

        $prompt = "Consider the ultimate objective of your team: {$this->objective}."
            .'Based on the result from previous tasks, you need to determine if the objective has been achieved.'
            ."The previous tasks are: {$achievedTasks}."
            ."Remaining tasks: {$unachievedTasks}."
            ."If the objective has been completed, give the exact answer to the objective {$this->objective}.";

        $model->generateTextOrReturnFunctionCalled($prompt);

        return null;
    }

    private function checkForCancellation(): void
    {

        // You can uncomment this and add a CONTROL_FILE_PATH const to have a mean of controlling the execution of stopping AutoPHP in the background
        // without killing the process

        //        if (file_exists(self::CONTROL_FILE_PATH)) {
        //            $content = file_get_contents(self::CONTROL_FILE_PATH);
        //            if (! $content) {
        //                echo json_encode(['end' => 'control file empty or not readable']);
        //                exit();
        //            }
        //            if (trim($content) !== 'ok') {
        //                echo json_encode(['end' => 'control file not ok']);
        //                exit();
        //            }
        //        }
        //
        //        echo json_encode(['end' => 'end']);
        //        exit();
    }
}
