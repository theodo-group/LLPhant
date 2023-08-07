<?php

namespace LLPhant\DataReader;

interface DataReader
{
    /**
     * @return Document[]
     */
    public function getDocuments(): array;
}
