<?php

namespace LLPhant\Tool;

use LLPhant\Chat\Function\FunctionBuilder;
use LLPhant\Chat\Function\FunctionInfo;

class ToolManager
{
    /**
     * @return FunctionInfo[]
     */
    public static function getAllToolsFunction(): array
    {
        $searchApi = new SerpApiSearch();
        $function = FunctionBuilder::buildFunctionInfo($searchApi, 'search');

        return [$function];
    }
}
