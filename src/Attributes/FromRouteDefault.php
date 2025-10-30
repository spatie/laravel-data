<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Illuminate\Http\Request;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Skipped;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromRouteDefault implements InjectsPropertyValue
{
    public function __construct(
        public string $routeParameter,
        public bool $replaceWhenPresentInPayload = true,
    ) {
    }

    public function resolve(
        DataProperty $dataProperty,
        mixed $payload,
        array $properties,
        CreationContext $creationContext
    ): mixed {
        if (! $payload instanceof Request) {
            return Skipped::create();
        }

        $route = $payload->route();

        // Only get from route defaults
        $defaults = $route->defaults;

        if (isset($defaults[$this->routeParameter])) {
            return $defaults[$this->routeParameter];
        }

        return Skipped::create();
    }

    public function shouldBeReplacedWhenPresentInPayload(): bool
    {
        return $this->replaceWhenPresentInPayload;
    }
}
