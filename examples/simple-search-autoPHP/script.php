<?php

use LLPhant\Chat\FunctionInfo\FunctionBuilder;
use LLPhant\Experimental\Agent\AutoPHP;
use LLPhant\Tool\SerpApiSearch;

require_once 'vendor/autoload.php';

// You describe the objective
$objective = 'find the name of wives or girlfriends from at least 2 players from the 2023 male french football team';

// You can add tools to the agent, so it can use them. You need an API key to use SerpApiSearch
// Have a look here: https://serpapi.com
$searchApi = new SerpApiSearch();
$function = FunctionBuilder::buildFunctionInfo($searchApi, 'googleSearch');

$autoPHP = new AutoPHP($objective, [$function]);
$autoPHP->run();
