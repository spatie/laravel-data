<?php

namespace Spatie\LaravelData\Support\Transformation;

use Closure;
use Spatie\LaravelData\Support\Partials\PartialsDefinition;
use Spatie\LaravelData\Support\Wrapping\Wrap;

class DataContext
{
    public function __construct(
        public PartialsDefinition $partialsDefinition = new PartialsDefinition(),
        public ?Wrap $wrap = null,
    ) {
    }
}
