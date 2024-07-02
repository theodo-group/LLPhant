<?php

use LLPhant\Chat\FunctionInfo\FunctionBuilder;
use LLPhant\Experimental\Agent\AutoPHP;
use LLPhant\Tool\WebPageTextGetter;

require_once 'vendor/autoload.php';

$webPageTextGetter = new WebPageTextGetter(true);

// This question have already been ingested at the moment
//$objective = 'Find the name of the French player who scored the most trials in the 2023 rugby world cup, you have to use exactly this page: https://fr.wikipedia.org/wiki/%C3%89quipe_de_France_de_rugby_%C3%A0_XV_%C3%A0_la_Coupe_du_monde_2023 .';
$objective = 'Find the name of the French top scorer during the Euro 2024 UEFA championship. You can use this page to retrieve fresh data on the topic: https://en.wikipedia.org/wiki/UEFA_Euro_2024';

$function = FunctionBuilder::buildFunctionInfo($webPageTextGetter, 'getWebPageText');

$autoPHP = new AutoPHP($objective, [$function], true);
$response = $autoPHP->run();
