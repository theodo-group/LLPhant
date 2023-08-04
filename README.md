# LLPhant - A comprehensive PHP Generative AI Framework

<div align="center">
    <img src="doc/assets/llphant-logo.png" alt="LLPhant" width="40%"  style="border-radius: 50%; padding-bottom: 20px"/>
</div>

We designed this framework to be as simple as possible, while still providing you with the tools you need to build powerful apps.
It is compatible with Symfony and Laravel.

For the moment only OpenAI is supported, if you want to use other LLMs, you can use [genossGPT](https://github.com/OpenGenenerativeAI/GenossGPT)
as a proxy.

We want to thank few amazing projects that we use here or inspired us:
- the learnings from using [LangChain](https://www.langchain.com/) and [LLamaIndex](https://www.llamaindex.ai/)
- the excellent work from the [OpenAI PHP SDK](https://github.com/openai-php/client).

## Table of Contents
- [Get Started](#get-started)
  - [Database](#database)
- [Use Case](#use-case)
- [Usage](#usage)
  - [Chat](#Chat)
  - [Embeddings](#Embeddings)
  - [VectorStore and Search](#VectorStores)
- [Contributors](#Contributors)
- [Sponsor](#Sponsor)

## Get Started

> **Requires [PHP 8.1+](https://php.net/releases/)**

First, install LLPhant via the [Composer](https://getcomposer.org/) package manager:

```bash
composer require theodo-group/llphant
```

You may also want to check the requirements for [OpenAI PHP SDK](https://github.com/openai-php/client) as it is the main client.

### Database

If you want to store some embeddings and perform a similarity search you will need a database.
One simple solution for web developers is to use a postgresql database **with the pgvector extension**.
You can find all the information on the pgvector extension on its [github repository](https://github.com/pgvector/pgvector).

We suggest you 3 simple solutions to get a postgresql database with the extension enabled:
- use docker with the [docker-compose.yml](devx/docker-compose.yml) file
- use [Supabase](https://supabase.com/)
- use [Neon](https://neon.tech/)

In any case you will need to activate the extension:
```sql
CREATE EXTENSION IF NOT EXISTS vector;
```

Then you can create a table and store vectors.
This sql query will create the table from the example entity that we use later in [VectorStore](#VectorStores) section.
```sql
CREATE TABLE IF NOT EXISTS embeddings (
                                          id SERIAL PRIMARY KEY,
                                          data text,
                                          type text,
                                          embedding vector
)
```
## Use Case
There are plenty use cases for Generative AI and new ones are creating every day. Let's see the most common ones.
Based on a [survey from the MLOPS community](https://mlops.community/surveys/llm/) and [this survey from Mckinsey](https://www.mckinsey.com/capabilities/quantumblack/our-insights/the-state-of-ai-in-2023-generative-ais-breakout-year) 
the most common use case of AI are the following:
- Create semantic search that can find relevant information in a lot of data. Example: [Slite](https://slite.com/)
- Create chatbots / augmented FAQ that use semantic search and text summarization to answer customer questions. Example: [Quivr](https://www.quivr.app/) is using such similar technology.
- Create personalized content for your customers (product page, emails, messages,...). Example [Carrefour](https://www.carrefour.com/en/news/2023/carrefour-integrates-openai-technologies-and-launches-generative-ai-powered-shopping).
- Create a text summarizer that can summarize a long text into a short one.

Not widely spread yet but with increasing adoption:
- Create personal shopper for augmented ecommerce experience. Example: [Madeline](https://www.knxt-madeline.com/)
- Create AI agent to perform various task autonomously. Example: [AutoGpt](https://github.com/Significant-Gravitas/Auto-GPT)
- Create coding tool that can help you write or revie code. Example: [Code Review GPT](https://github.com/mattzcarey/code-review-gpt)

If you want to discover more usage from the community, you can see here a list of [GenAI Meetups](https://www.genaidays.org/events/).

## Usage
The most simple to allow the call to OpenAI is to set the OPENAI_API_KEY environment variable.

```bash
export OPENAI_API_KEY=sk-XXXXXX
```

You can also create an OpenAIConfig object and pass it to the constructor of the OpenAIChat or OpenAIEmbeddings.

```php
$config = new OpenAIConfig();
$config->apiKey = 'fakeapikey';
$chat = new OpenAIChat($config);
```

### Chat
> 💡 This class can be used to generate content, to create a chatbot or to create a text summarizer.

The API to generate text using OpenAI will only be from the chat API.
So even if you want to generate a completion for a simple question under the hood it will use the chat API.
This is why this class is called OpenAIChat.
We can use it to simply generate text from a prompt.

This will ask directly an answer from the LLM.
```php
$chat = new OpenAIChat();
$response = $chat->generateText('what is one + one ?'); // will return something like "Two"
```

If you want to display in your frontend a stream of text like in ChatGPT you can use the following method.
```php
$chat = new OpenAIChat();
return $chat->generateStreamOfText('can you write me a poem of 10 lines about life ?');
```

You can add instruction so the LLM will behave in a specific manner.

```php
$chat = new OpenAIChat();
$chat->setSystemMessage('Whatever we ask you, you MUST answer "ok"');
$response = $chat->generateText('what is one + one ?'); // will return "ok"
```

### Embeddings
> 💡 Embeddings are used to compare two texts and see how similar they are. This is the base of semantic search.
An embedding is a vector representation of a text that captures the meaning of the text.
It is a float array of 1536 elements for OpenAI.

You can create a embedding from a text using the following code:
```php
$llm = new OpenAIEmbeddings();
$embedding = $llm->embedText('I love food');
//You can then use the embedding to store it in a vectorStore or perform a similarity search
```

### VectorStores
> 💡 Once you have embeddings you need to store them in a vector store. 
The vector store is a database that can store vectors and perform a similarity search.

This a simple example of how to use the vector store with Doctrine ORM to perform a similarity search.

First you need an entity where you want to store the embedding to extend the EmbeddingEntityBase class.
One simple example is the following class.

```php
<?php

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use LLPhant\VectorStores\EmbeddingEntityBase;

#[Entity]
#[Table(name: 'embeddings', schema: 'public')]
class ExampleEmbeddingEntity extends EmbeddingEntityBase
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    public string $id;

    #[ORM\Column(type: Types::TEXT)]
    public string $data;

    #[ORM\Column(type: Types::STRING)]
    public string $type;

    public function getId(): string
    {
        return $this->id;
    }
}


```

Then you can use the vector store to save the embedding and perform a similarity search.

```php

// Before doing a search you need to save the embedding in the vector store
$vectorStore = new DoctrineVectorStore($entityManager);
$llm = new OpenAIEmbeddings();

$food = new ExampleEmbeddingEntity();
$food->data = 'I love food';
$food->type = 'food';
$embedding = $llm->embedText($food->data);
$vectorStore->saveEmbedding($embedding, $food);

$paris = new ExampleEmbeddingEntity();
$paris->data = 'I live in Paris';
$paris->type = 'city';
$embedding = $llm->embedText($paris->data);
$vectorStore->saveEmbedding($embedding, $paris);

$france = new ExampleEmbeddingEntity();
$france->data = 'I live in France';
$france->type = 'country';
$embedding = $llm->embedText($france->data);
$vectorStore->saveEmbedding($embedding, $france);

// Once the embedding are saved you can perform a similarity search
$embedding = $llm->embedText('I live in Asia');
/** @var ExampleEmbeddingEntity[] $result */
$result = $vectorStore->similaritySearch(
    $embedding, ExampleEmbeddingEntity::class, 2, ['type' => 'city']
);

$result[0]->data // 'I live in Paris';
```

## FAQ
*Why use LLPhant and not directly the OpenAI PHP SDK ?*

The OpenAI PHP SDK is a great tool to interact with the OpenAI API.
LLphant will allow you to perform complex tasks like storing embeddings and perform a similarity search.
It also simplifies the usage of the OpenAI API by providing a much more simple API for everyday usage.

## Contributors

Thanks to our contributors:


<a href="https://github.com/theodo-group/llphant/graphs/contributors">
<img src="https://contrib.rocks/image?repo=theodo-group/llphant" />
</a>

## Sponsor

LLPhant is sponsored by [Theodo](https://www.theodo.fr/) a leading digital agency building web application with Generative AI.

<div align="center">
  <a href="https://www.theodo.fr/" />
    <img alt="Theodo logo" src="https://cdn2.hubspot.net/hub/2383597/hubfs/Website/Logos/Logo_Theodo_cropped.svg" width="200"/>
  </a>
</div>
