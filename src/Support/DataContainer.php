<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Resolvers\RequestQueryStringPartialsResolver;
use Spatie\LaravelData\Resolvers\TransformedDataCollectionResolver;
use Spatie\LaravelData\Resolvers\TransformedDataResolver;

class DataContainer
{
    protected static self $instance;

    protected ?TransformedDataResolver $transformedDataResolver = null;

    protected ?TransformedDataCollectionResolver $transformedDataCollectionResolver = null;

    protected ?RequestQueryStringPartialsResolver $requestQueryStringPartialsResolver = null;

    private function __construct()
    {
    }

    public static function get(): DataContainer
    {
        if (! isset(static::$instance)) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    public function transformedDataResolver(): TransformedDataResolver
    {
        return $this->transformedDataResolver ??= app(TransformedDataResolver::class);
    }

    public function transformedDataCollectionResolver(): TransformedDataCollectionResolver
    {
        return $this->transformedDataCollectionResolver ??= app(TransformedDataCollectionResolver::class);
    }

    public function requestQueryStringPartialsResolver(): RequestQueryStringPartialsResolver
    {
        return $this->requestQueryStringPartialsResolver ??= app(RequestQueryStringPartialsResolver::class);
    }

    public function reset()
    {
        $this->transformedDataResolver = null;
        $this->transformedDataCollectionResolver = null;
        $this->requestQueryStringPartialsResolver = null;
    }
}
