<?php

namespace Tests\Unit\Chat;

use OpenAI\Contracts\Resources\ChatContract;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Meta\MetaInformation;
use OpenAI\Responses\StreamResponse;
use OpenAI\Testing\Responses\Concerns\Fakeable;
use OpenAI\Testing\Responses\Fixtures\Chat\CreateResponseFixture;

class MockOpenAIChat implements ChatContract
{
    use Fakeable;

    public function create(array $parameters): CreateResponse
    {
        return CreateResponse::from(CreateResponseFixture::ATTRIBUTES, self::fakeResponseMetaInformation());
    }

    public function createStreamed(array $parameters): StreamResponse
    {
        // TODO: Implement createStreamed() method.
    }
}
