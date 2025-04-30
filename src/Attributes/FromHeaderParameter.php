<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Attributes\InjectsPropertyValue;
use Illuminate\Http\Request;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Skipped;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromHeaderParameter implements InjectsPropertyValue
{
    public function __construct(
        public string $headerParameter,
        public bool $replaceWhenPresentInPayload = true,
        /** @deprecated  */
        public bool $replaceWhenPresentInBody = true
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

        $parameter = $payload->header($this->headerParameter);

        if ($parameter === null) {
            return Skipped::create();
        }

        return $parameter;
    }

    public function shouldBeReplacedWhenPresentInPayload(): bool
    {
        return $this->replaceWhenPresentInPayload && $this->replaceWhenPresentInBody;
    }
}
