<?php

namespace LLPhant\Image;

use Exception;
use LLPhant\Image\Enums\OpenAIImageModel;
use LLPhant\Image\Enums\OpenAIImageSize;
use LLPhant\OpenAIConfig;
use OpenAI;
use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Images\CreateResponse;

use function getenv;

class OpenAIImage implements ImageInterface
{
    private readonly ClientContract $client;

    public string $model;

    /** @var array<string, mixed> */
    private array $modelOptions = [];

    public function __construct(?OpenAIConfig $config = null)
    {
        if ($config instanceof OpenAIConfig && $config->client instanceof ClientContract) {
            $this->client = $config->client;
        } else {
            $apiKey = $config->apiKey ?? getenv('OPENAI_API_KEY');
            if (! $apiKey) {
                throw new Exception('You have to provide a OPENAI_API_KEY env var to request OpenAI .');
            }

            $this->client = OpenAI::client($apiKey);
        }
        $this->model = $config->model ?? OpenAIImageModel::DallE3->getModelName();
        $this->modelOptions = $config->modelOptions ?? [];
    }

    public function generateImage(string $prompt): Image
    {
        $answer = $this->client->images()->create([
            'prompt' => $prompt,
            'model' => $this->model,
            'n' => 1,
            'size' => $this->modelOptions['size'] ?? OpenAIImageSize::size_1024x1024->getSize(),
        ]);

        return $this->responseToImage($answer);
    }

    public function setModelOption(string $option, mixed $value): void
    {
        $this->modelOptions[$option] = $value;
    }

    private function responseToImage(CreateResponse $answer): Image
    {
        $data = $answer->data[0];

        $image = new Image();
        $image->url = $data->url;
        $image->revisedPrompt = $data->revisedPrompt;

        return $image;
    }
}
