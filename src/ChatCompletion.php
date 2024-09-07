<?php

namespace ketchalegend\LaravelTogetherAI;

use Illuminate\Support\Facades\Config;

class ChatCompletion
{
    protected $togetherAI;

    public function __construct(TogetherAI $togetherAI)
    {
        $this->togetherAI = $togetherAI;
    }

    public function create(array $options)
    {
        $defaultOptions = [
            'model' => Config::get('together-ai.default_model'),
            'max_tokens' => Config::get('together-ai.max_tokens'),
            'temperature' => Config::get('together-ai.temperature'),
            'top_p' => Config::get('together-ai.top_p'),
            'top_k' => Config::get('together-ai.top_k'),
            'repetition_penalty' => Config::get('together-ai.repetition_penalty'),
            'stop' => Config::get('together-ai.stop'),
            'stream' => false
        ];

        $data = array_merge($defaultOptions, $options);

        return $this->togetherAI->sendRequest('v1/chat/completions', $data);
    }
}
