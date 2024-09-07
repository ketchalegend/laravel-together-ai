<?php

namespace ketchalegend\LaravelTogetherAI;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use ketchalegend\LaravelTogetherAI\ChatCompletion;

class TogetherAI
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;
    protected $headers = [];

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'];
        $this->baseUrl = $config['base_url'];
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
        $this->initializeClient();
    }

    protected function initializeClient()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $this->headers,
        ]);
    }

    public function withHttpHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        $this->initializeClient();
        return $this;
    }

    public function withBaseUri(string $baseUri): self
    {
        $this->baseUrl = $baseUri;
        $this->initializeClient();
        return $this;
    }

    public function withApiKey(string $apiKey): self
    {
        $this->apiKey = trim($apiKey);
        $this->headers['Authorization'] = 'Bearer ' . $this->apiKey;
        $this->initializeClient();
        return $this;
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

    public function chat()
    {
        return new ChatCompletion($this);
    }

    public function sendRequest($endpoint, $data)
    {
        $response = $this->client->post($endpoint, [
            'json' => $data,
        ]);

        return $this->parseResponse($response, $data['stream'] ?? false);
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