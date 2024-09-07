<?php

namespace ketchalegend\LaravelTogetherAI\Facades;

use Illuminate\Support\Facades\Facade;

class TogetherAI extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'together-ai';
    }
}