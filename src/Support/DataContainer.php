<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Resolvers\TransformedDataCollectionResolver;
use Spatie\LaravelData\Resolvers\TransformedDataResolver;

class DataContainer
{
    private static self $instance;

    private TransformedDataResolver $transformedDataResolver;

    private TransformedDataCollectionResolver $transformedDataCollectionResolver;

    private PartialsParser $partialsParser;

    private function __construct()
    {
    }

    public static function get(): DataContainer
    {
        if (! isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function transformedDataResolver(): TransformedDataResolver
    {
        if (! isset($this->transformedDataResolver)) {
            $this->transformedDataResolver = app(TransformedDataResolver::class);
        }

        return $this->transformedDataResolver;
    }

    public function transformedDataCollectionResolver(): TransformedDataCollectionResolver
    {
        if (! isset($this->transformedDataCollectionResolver)) {
            $this->transformedDataCollectionResolver = app(TransformedDataCollectionResolver::class);
        }

        return $this->transformedDataCollectionResolver;
    }

    public function partialsParser(): PartialsParser
    {
        if (! isset($this->partialsParser)) {
            $this->partialsParser = app(PartialsParser::class);
        }

        return $this->partialsParser;
    }
}
