<?php

use LLPhant\Experimental\Agent\AutoPHP;
use LLPhant\Tool\ToolManager;

require_once 'vendor/autoload.php';

$objective = 'find the name of wives or girlfriends from at least 2 players from the 2023 male french football team';
$tools = ToolManager::getAllToolsFunction();
$autoPHP = new AutoPHP($objective, $tools);
$autoPHP->run();
