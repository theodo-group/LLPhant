<?php

namespace LLPhant\Experimental\Agent;

class AutoPHPInternalTool
{
    /**
     * This function is used to know if we have achieved the objective.
     *
     * @return array{objectiveCompleted: bool, answer: string}
     */
    public function objectiveStatus(bool $objectiveCompleted, string $answer): array
    {
        return ['objectiveCompleted' => $objectiveCompleted, 'answer' => $answer];
    }
}
