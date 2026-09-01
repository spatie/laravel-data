<?php

namespace Spatie\LaravelData\Attributes;

use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

interface InjectsPropertyValue
{
    public function resolve(
        DataProperty $dataProperty,
        CreationContext $creationContext
    ): mixed;

    public function shouldBeReplacedWhenPresentInPayload(): bool;
}
