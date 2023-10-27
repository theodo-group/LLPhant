<?php

use LLPhant\Chat\FunctionInfo\FunctionBuilder;
use LLPhant\Experimental\Agent\AutoPHP;
use LLPhant\Tool\WebPageTextGetter;

require_once 'vendor/autoload.php';

$webPageTextGetter = new WebPageTextGetter(true);

$objective = 'Find the name of the french player who scored the most trials in the 2023 rugby world cup, you have to use this page: https://fr.wikipedia.org/wiki/%C3%89quipe_de_France_de_rugby_%C3%A0_XV_%C3%A0_la_Coupe_du_monde_2023 .';

$function = FunctionBuilder::buildFunctionInfo($webPageTextGetter, 'getWebPageText');

$autoPHP = new AutoPHP($objective, [$function], true, 1);
$response = $autoPHP->run();
