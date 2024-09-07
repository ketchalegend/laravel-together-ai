# Laravel Together AI

This package provides a simple way to interact with the Together AI API in your Laravel application.

## Installation

You can install the package via composer:

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

```php
use ketchalegend\LaravelTogetherAI\Facades\TogetherAI;

// For a regular chat completion
$response = TogetherAI::chat([
    ['role' => 'user', 'content' => 'Hello, how are you?']
], [
    'temperature' => 0.8,
    'max_tokens' => 100
]);

// For a streamed chat completion
$stream = TogetherAI::streamChat([
    ['role' => 'user', 'content' => 'Tell me a story']
]);

foreach ($stream as $chunk) {
    echo $chunk['choices'][0]['delta']['content'] ?? '';
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.