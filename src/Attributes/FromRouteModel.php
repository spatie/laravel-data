<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromRouteModel
{
    public function __construct(
        public string  $routeModel,
        public ?string $routeModelProperty = null,
        public bool    $replace = true,
    ) {
    }
}
