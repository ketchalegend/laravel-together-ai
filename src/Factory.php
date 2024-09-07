<?php

namespace ketchalegend\LaravelTogetherAI;

use ketchalegend\LaravelTogetherAI\TogetherAI;

class Factory
{
    private $instance;

    public function __construct()
    {
        $this->instance = TogetherAI::create();
    }

    public function withApiKey(string $apiKey): self
    {
        $this->instance->withApiKey($apiKey);
        return $this;
    }

    public function withBaseUri(string $baseUri): self
    {
        $this->instance->withBaseUri($baseUri);
        return $this;
    }

    public function withHttpHeader(string $name, string $value): self
    {
        $this->instance->withHttpHeader($name, $value);
        return $this;
    }

    public function make(): TogetherAI
    {
        return $this->instance;
    }
}
