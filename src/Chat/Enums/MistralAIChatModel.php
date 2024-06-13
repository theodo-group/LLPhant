<?php

namespace LLPhant\Chat\Enums;

enum MistralAIChatModel: string
{
    case tiny = 'mistral-tiny';
    case small = 'mistral-small-latest';
    case medium = 'mistral-medium-latest';
    case large = 'mistral-large-latest';
}
