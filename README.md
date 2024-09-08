# Laravel Together AI

This package provides an easy integration with the Together AI API for your Laravel application.

## Installation

Install the package via Composer:

```bash
composer require ketchalegend/laravel-together-ai
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="ketchalegend\LaravelTogetherAI\TogetherAIServiceProvider" --tag="together-ai-config"
```

Then, add your Together AI API key to your `.env` file:

```
TOGETHER_AI_API_KEY=your-api-key-here
```

## Usage

Here are various examples of how to use the Laravel Together AI package:

```php

use ketchalegend\LaravelTogetherAI\Facades\TogetherAI;

// Basic chat completion
$response = TogetherAI::chat()->create([
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, how are you?']
    ]
]);

// Chat completion with custom parameters
$response = TogetherAI::chat()->create([
    'messages' => [
        ['role' => 'user', 'content' => 'Explain quantum computing in simple terms.']
    ],
    'temperature' => 0.8,
    'max_tokens' => 150
]);

// Chat with system message and multiple user messages
$response = TogetherAI::chat()->create([
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful assistant specializing in technology.'],
        ['role' => 'user', 'content' => 'What is cloud computing?'],
        ['role' => 'assistant', 'content' => 'Cloud computing is a technology that allows users to access and use computing resources over the internet instead of on their local computer.'],
        ['role' => 'user', 'content' => 'What are its main benefits?']
    ]
]);

// Streamed chat completion
$stream = TogetherAI::chat()->create([
    'messages' => [
        ['role' => 'user', 'content' => 'Tell me a short story about a robot.']
    ],
    'stream' => true
]);

foreach ($stream as $chunk) {
    if (isset($chunk['done']) && $chunk['done']) {
        echo "Story finished.\n";
        break;
    }
    echo $chunk['choices'][0]['delta']['content'] ?? '';
}

// Chat completion with function calling (if supported by Together AI)
$functions = [
    [
        'name' => 'get_current_weather',
        'description' => 'Get the current weather in a given location',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'The city and state, e.g. San Francisco, CA',
                ],
                'unit' => ['type' => 'string', 'enum' => ['celsius', 'fahrenheit']],
            ],
            'required' => ['location'],
        ],
    ]
];

$response = TogetherAI::chat()->create([
    'messages' => [
        ['role' => 'user', 'content' => 'What\'s the weather like in New York?']
    ],
    'functions' => $functions,
    'function_call' => 'auto'
]);

// Handle the response
if (isset($response['choices'][0]['function_call'])) {
    $functionCall = $response['choices'][0]['function_call'];
    // Process the function call...
} else {
    echo $response['choices'][0]['message']['content'];
}

// Error handling
try {
    $response = TogetherAI::chat()->create([
        'messages' => [
            ['role' => 'user', 'content' => 'Generate a very long response.']
        ],
        'max_tokens' => 10000 // Assuming this exceeds the API's limit
    ]);
} catch (\Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}

// Using custom headers and base URI   

$client = TogetherAI::factory()
    ->withApiKey($newApiKey)
    ->withBaseUri($newBaseUri)
    ->withHttpHeader('Custom-Header', 'Custom-Value');
    ->make();

$response = $client->chat()->create([
    'messages' => [
        ['role' => 'user', 'content' => 'Hello with custom configuration!']
    ]
]);
```

These examples demonstrate:

1. Basic chat completion
2. Chat with custom parameters
3. Multi-turn conversations with system and assistant messages
4. Streamed responses
5. Function calling
6. Simplified input for user messages
7. Error handling

Remember to handle API responses and errors appropriately in your application. The actual structure of the response may vary depending on the Together AI API version and the specific model used.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.