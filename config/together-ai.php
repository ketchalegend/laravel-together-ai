<?php

return [
    'api_key' => env('TOGETHER_AI_API_KEY'),
    'base_url' => env('TOGETHER_AI_BASE_URL', 'https://api.together.xyz/'),
    'default_model' => env('TOGETHER_AI_DEFAULT_MODEL', 'meta-llama/Meta-Llama-3.1-8B-Instruct-Turbo'),
    'max_tokens' => env('TOGETHER_AI_MAX_TOKENS', 1024),
    'temperature' => env('TOGETHER_AI_TEMPERATURE', 0.7),
    'top_p' => env('TOGETHER_AI_TOP_P', 0.7),
    'top_k' => env('TOGETHER_AI_TOP_K', 50),
    'repetition_penalty' => env('TOGETHER_AI_REPETITION_PENALTY', 1),
    'stop' => ["<|eot_id|>", "<|eom_id|>"],
    'max_retries' => env('TOGETHER_AI_MAX_RETRIES', 3),
    'retry_delay' => env('TOGETHER_AI_RETRY_DELAY', 1000)
];
