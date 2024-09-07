<?php

namespace ketchalegend\LaravelTogetherAI;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class TogetherAI
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'];
        $this->baseUrl = $config['base_url'];
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function chat(array $messages, array $options = [], array $functions = null)
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

        $data = array_merge($defaultOptions, $options, ['messages' => $this->formatMessages($messages)]);

        if ($functions !== null) {
            $data['functions'] = $functions;
        }

        $response = $this->client->post('v1/chat/completions', [
            'json' => $data,
        ]);

        return $this->parseResponse($response, $data['stream']);
    }

    public function streamChat(array $messages, array $options = [], array $functions = null)
    {
        $options['stream'] = true;
        return $this->chat($messages, $options, $functions);
    }

    protected function formatMessages(array $messages)
    {
        $formattedMessages = [];
        foreach ($messages as $message) {
            if (is_string($message)) {
                $formattedMessages[] = ['role' => 'user', 'content' => $message];
            } elseif (is_array($message) && isset($message['role']) && isset($message['content'])) {
                $formattedMessages[] = $message;
            }
        }
        return $formattedMessages;
    }

    protected function parseResponse($response, $isStream)
    {
        if ($isStream) {
            return $this->parseStreamedResponse($response);
        } else {
            return json_decode($response->getBody(), true);
        }
    }

    protected function parseStreamedResponse($response)
    {
        $buffer = "";
        $response->getBody()->rewind();

        while (!$response->getBody()->eof()) {
            $buffer .= $response->getBody()->read(1024);
            $lines = explode("\n", $buffer);

            foreach ($lines as $i => $line) {
                if (strpos($line, 'data: ') === 0) {
                    $jsonData = substr($line, 6); // Remove "data: " prefix
                    if ($jsonData === "[DONE]") {
                        yield ['done' => true];
                    } else {
                        yield json_decode($jsonData, true);
                    }
                }
            }

            $buffer = $lines[count($lines) - 1];
        }
    }
}
