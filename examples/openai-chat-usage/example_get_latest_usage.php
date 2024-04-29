<?php 
use LLPhant\Chat\OpenAIChat;
use LLPhant\OpenAIConfig;
/* Example of getting usage from last response */
require_once 'vendor/autoload.php';

$config = new OpenAIConfig();
$config->apiKey = 'api key';

$client = new OpenAIChat($config);

$response = $client->generateText('Hello!');

$prompt_tokens = $client->usage->Prompt_Tokens;
$completion_tokens = $client->usage->Completion_Tokens;
$total_tokens = $client->usage->Total_Tokens;

echo $response . '<br>Prompt tokens:' . $prompt_tokens . 
'<br>Completion Tokens:' . $completion_tokens . '<br>Total tokens:' . $total_tokens;
