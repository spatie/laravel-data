<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromRouteParameterProperty
{
    public function __construct(
        public string $routeParameter,
        public ?string $property = null,
        public bool $replaceWhenPresentInBody = true,
    ) {
    }
}
