<?php

use LLPhant\Chat\FunctionInfo\FunctionBuilder;
use LLPhant\Experimental\Agent\AutoPHP;
use LLPhant\Tool\WebPageTextGetter;

require_once 'vendor/autoload.php';

$webPageTextGetter = new WebPageTextGetter(true);

// This question have already been ingested at the moment
$objective = 'Find the name of the French player who scored the most trials in the 2023 rugby world cup, you have to use exactly this page: https://fr.wikipedia.org/wiki/%C3%89quipe_de_France_de_rugby_%C3%A0_XV_%C3%A0_la_Coupe_du_monde_2023 .';
$objective .= ' DO NOT TRY TO VERYFY THE ANSWER USING DATA OUTSIDE THE GIVEN URL!';
$function = FunctionBuilder::buildFunctionInfo($webPageTextGetter, 'getWebPageText');

$autoPHP = new AutoPHP($objective, [$function], true);
$response = $autoPHP->run(10);
