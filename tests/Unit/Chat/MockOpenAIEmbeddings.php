<?php

namespace Tests\Unit\Chat;

use OpenAI\Contracts\Resources\EmbeddingsContract;
use OpenAI\Responses\Embeddings\CreateResponse;
use OpenAI\Testing\Responses\Concerns\Fakeable;
use OpenAI\Testing\Responses\Fixtures\Embeddings\CreateResponseFixture;

class MockOpenAIEmbeddings implements EmbeddingsContract
{
    use Fakeable;

    public function create(array $parameters): CreateResponse
    {
        return CreateResponse::from(CreateResponseFixture::ATTRIBUTES, self::fakeResponseMetaInformation());
    }
}
