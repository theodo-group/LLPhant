<?php

namespace LLPhant\Chat\Enums;

enum ChatRole: string
{
    case System = "system";
    case User = "user";
    case Assistant = "assitant";
    case Function = "function";
}
