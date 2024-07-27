<?php

namespace LLPhant\Chat\Enums;


enum CloudflareChatModel
{
    case Lama27bf16;
    case Llama27bInt8;
    case Mistral7bv1;
    case DeepseekCoder67bBaseAwq;
    case DeepseekCoder67bInstructAwq;
    case DeepseekMath7bBase;
    case DeepseekMath7bInstruct;
    case DiscolmGerman7bV1Awq;
    case Falcon7bInstruct;
    case Gemma2B;
    case Gemma7BIt;
    case Gemma7BItLora;
    case Hermes2ProMistral7B;
    case Llama213bChatAwq;
    case Llama27bChatHfLora;
    case Llamaguard7bAwq;
    case Mistral7bInstructV01Awq;
    case Mistral7bInstructV02;
    case Mistral7bInstructV02Lora;
    case NeuralChat7bV31Awq;
    case Openchat350106;
    case Openhermes25Mistral7bAwq;
    case Phi2;
    case Qwen15bChat;
    case Qwen1818bChat;
    case Qwen11514bChatAwq;
    case Qwen157bChatAwq;
    case Sqlcoder7b2;
    case StarlingLm7bBeta;
    case Tinyllama11bChatV10;
    case Zephyr7bBetaAwq;

    public function getModelName(): string
    {
        return match ($this) {
            CloudflareChatModel::Lama27bf16 => 'llama-2-7b-chat-fp16',
            CloudflareChatModel::Llama27bInt8 => 'llama-2-7b-chat-int8',
            CloudflareChatModel::Mistral7bv1 => 'mistral-7b-instruct-v0.1',
            CloudflareChatModel::DeepseekCoder67bBaseAwq => 'deepseek-coder-6.7b-base-awq',
            CloudflareChatModel::DeepseekCoder67bInstructAwq => 'deepseek-coder-6.7b-instruct-awq',
            CloudflareChatModel::DeepseekMath7bBase => 'deepseek-math-7b-base',
            CloudflareChatModel::DeepseekMath7bInstruct => 'deepseek-math-7b-instruct',
            CloudflareChatModel::DiscolmGerman7bV1Awq => 'discolm-german-7b-v1-awq',
            CloudflareChatModel::Falcon7bInstruct => 'falcon-7b-instruct',
            CloudflareChatModel::Gemma2B => 'gemma-2b-it-lora',
            CloudflareChatModel::Gemma7BIt => 'gemma-7b-it',
            CloudflareChatModel::Gemma7BItLora => 'gemma-7b-it-lora',
            CloudflareChatModel::Hermes2ProMistral7B => 'hermes-2-pro-mistral-7b',
            CloudflareChatModel::Llama213bChatAwq => 'llama-2-13b-chat-awq',
            CloudflareChatModel::Llama27bChatHfLora => 'llama-2-7b-chat-hf-lora',
            CloudflareChatModel::Llamaguard7bAwq => 'llamaguard-7b-awq',
            CloudflareChatModel::Mistral7bInstructV01Awq => 'mistral-7b-instruct-v0.1-awq',
            CloudflareChatModel::Mistral7bInstructV02 => 'mistral-7b-instruct-v0.2',
            CloudflareChatModel::Mistral7bInstructV02Lora => 'mistral-7b-instruct-v0.2-lora',
            CloudflareChatModel::NeuralChat7bV31Awq => 'neural-chat-7b-v3-1-awq',
            CloudflareChatModel::Openchat350106 => 'openchat-3.5-0106',
            CloudflareChatModel::Openhermes25Mistral7bAwq => 'openhermes-2.5-mistral-7b-awq',
            CloudflareChatModel::Phi2 => 'phi-2',
            CloudflareChatModel::Qwen15bChat => 'qwen1.5-0.5b-chat',
            CloudflareChatModel::Qwen1818bChat => 'qwen1.5-1.8b-chat',
            CloudflareChatModel::Qwen11514bChatAwq => 'qwen1.5-14b-chat-awq',
            CloudflareChatModel::Qwen157bChatAwq => 'qwen1.5-7b-chat-awq',
            CloudflareChatModel::Sqlcoder7b2 => 'sqlcoder-7b-2',
            CloudflareChatModel::StarlingLm7bBeta => 'starling-lm-7b-beta',
            CloudflareChatModel::Tinyllama11bChatV10 => 'tinyllama-1.1b-chat-v1.0',
            CloudflareChatModel::Zephyr7bBetaAwq => 'zephyr-7b-beta-awq',
        };
    }
}
