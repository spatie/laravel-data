<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromRouteModel
{
    public function __construct(
        public string  $routeParameter,
        public ?string $modelProperty = null,
        public bool    $replaceWhenPresentInBody = true,
    ) {
    }
}
