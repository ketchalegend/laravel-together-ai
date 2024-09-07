<?php

namespace ketchalegend\LaravelTogetherAI;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Exception\RequestException;
use ketchalegend\LaravelTogetherAI\Factory;
use ketchalegend\LaravelTogetherAI\ChatCompletion;

class TogetherAI
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;
    protected $headers = [];
    protected $maxRetries = 3;
    protected $retryDelay = 1000; 

    protected function __construct()
    {
        $this->baseUrl = 'https://api.together.xyz';
        $this->headers = [
            'Content-Type' => 'application/json',
        ];
        $this->maxRetries = config('together-ai.max_retries', 3);
        $this->retryDelay = config('together-ai.retry_delay', 1000);
    }

    public static function factory(): Factory
    {
        return new Factory();
    }

    public static function create(): self
    {
        return new self();
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
        return $this;
    }

    public function withBaseUri(string $baseUri): self
    {
        $this->baseUrl = $baseUri;
        return $this;
    }

    public function withApiKey(string $apiKey): self
    {
        $this->apiKey = trim($apiKey);
        $this->headers['Authorization'] = 'Bearer ' . $this->apiKey;
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

    public function setMaxRetries(int $maxRetries): self
    {
        $this->maxRetries = $maxRetries;
        return $this;
    }

    public function setRetryDelay(int $milliseconds): self
    {
        $this->retryDelay = $milliseconds;
        return $this;
    }

    public function sendRequest($endpoint, $data)
    {
        $this->initializeClient();

        $attempts = 0;
        while ($attempts < $this->maxRetries) {
            try {
                $response = $this->client->post($endpoint, [
                    'json' => $data,
                ]);
                $responseBody = json_decode($response->getBody(), true);

                if (empty($responseBody['choices'][0]['message']['content'])) {
                    Log::warning('Together AI returned an empty response content');
                }

                return $responseBody;
            } catch (RequestException $e) {
                $attempts++;

                if ($e->getResponse() && $e->getResponse()->getStatusCode() == 503) {
                    Log::warning("Together AI server overloaded. Attempt {$attempts} of {$this->maxRetries}");
                    if ($attempts < $this->maxRetries) {
                        usleep($this->retryDelay * 1000); // Convert to microseconds
                        continue;
                    }
                }

                Log::error('Together AI API Error: ' . $e->getMessage());
                if ($e->hasResponse()) {
                    Log::error('Response Body: ' . $e->getResponse()->getBody());
                }
                throw new \Exception('Error during chat completion: ' . $e->getMessage());
            }
        }

        throw new \Exception('Max retry attempts reached. Together AI server is still unavailable.');
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