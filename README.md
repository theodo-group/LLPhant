**LLPhant** is a community-maintained Web PHP Framework that allows you to build Generative AI apps.

We designed this framework to be as simple as possible, while still providing you with the tools you need to build powerful apps.
For the moment only OpenAI is supported, but we some project like [genossGPT](https://github.com/OpenGenenerativeAI/GenossGPT) the open source LLMs will be supported too.

We want thanks few amazing projects that we use here or inspired us:
- the learnings from using [LangChain](https://www.langchain.com/) and [LLamaIndex](https://www.llamaindex.ai/)
- the excellent work from the [OpenAI PHP SDK](https://github.com/openai-php/client).

## Table of Contents
- [Get Started](#get-started)
- [Usage](#usage)
  - [Chat](#Chat)
  - [Embeddings](#Embeddings)
  - [VectorStore and Search](#VectorStores)

## Get Started

> **Requires [PHP 8.1+](https://php.net/releases/)**

First, install LLPhant via the [Composer](https://getcomposer.org/) package manager:

```bash
composer require theodo-group/llphant
```

## Usage

### Chat

The API to generate text using OpenAI will only be from the chat API.
So even if you want to generate a completion for a simple question under the hood it will use the chat API.
This is why this class is called OpenAIChat.
We can use it to simply generate text from a prompt.

This will ask directly an answer from the LLM.
```php
    $chat = new OpenAIChat();
    $response = $chat->generateText('what is one + one ?'); // will return something like "Two"
```

You can add instruction so the LLM will behave in a specific manner.

```php
    $chat = new OpenAIChat();
    $chat->setSystemMessage('Whatever we ask you, you MUST answer "ok"');
    $response = $chat->generateText('what is one + one ?'); // will return "ok"
```

### Embeddings
An embedding is a vector representation of a text that captures the meaning of the text.
It is a float array of 1536 elements for OpenAI.
This vector representation can be used to compare two texts and see how similar they are.

```php
    $llm = new OpenAIEmbeddings();
    $embedding = $llm->embedText('I love food');
    // You can then use the embedding to store it in a vector store or perform a similarity search
```

### VectorStores

This a simple example of how to use the vector store with Doctrine ORM to perform a similarity search.

First you need an entity where you want to store the embedding to extend the EmbeddingEntityBase class.
One simple example is the following class.

```php
<?php

namespace Tests\E2E\VectorStores;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use LLPhant\VectorStores\EmbeddingEntityBase;
use Ramsey\Uuid\Doctrine\UuidGenerator;

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
$result = $vectorStore->similaritySearch($embedding, ExampleEmbeddingEntity::class, 2, ['type' => 'city']);

$result[0]->data // 'I live in Paris';
```

You can find us on Twitter: 
- [Maxime Thoonsen](https://twitter.com/maxthoon).
