<?php

require_once 'vendor/autoload.php';

use LLPhant\Chat\FunctionInfo\FunctionBuilder;
use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\Experimental\Agent\AutoPHP;
use LLPhant\Tool\SerpApiSearch;

// 3 model embedding OPENAI
// https://openai.com/blog/new-embedding-models-and-api-updates
// Old Model 'text-embedding-ada-002' (lenght 1536)
// New Model 'text-embedding-3-small' (lenght 512 -> 1536)
// New Model 'text-embedding-3-large' (lenght 256 -> 1024 -> 3072)


$dataReader = new FileDataReader(__DIR__ . '/best_practices.rst');
$documents = $dataReader->getDocuments();

$splittedDocuments = DocumentSplitter::splitDocuments($documents, 500);

$embeddingGenerator = new OpenAIEmbeddingGenerator();
$embeddingGenerator->modelName = 'text-embedding-3-small';
$embeddingGenerator->EmbeddingLength = 512;
$embeddedDocuments = $embeddingGenerator->embedDocuments($splittedDocuments);

$vectorStore = new FileSystemVectorStore();
$vectorStore->filePath = __DIR__ . '/documents-vectorStore.json';
$vectorStore->addDocuments($embeddedDocuments);

