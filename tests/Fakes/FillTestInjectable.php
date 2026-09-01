<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Attribute;
use Spatie\LaravelData\Attributes\InjectsPropertyValue;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Skipped;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class FillTestInjectable implements InjectsPropertyValue
{
    public function __construct(
        public string $value = 'injected',
        public bool $replace = true,
        public bool $skip = false,
    ) {
    }

    public function resolve(
        DataProperty $dataProperty,
        CreationContext $creationContext
    ): mixed {
        if ($this->skip) {
            return Skipped::create();
        }

        return $this->value;
    }

    public function shouldBeReplacedWhenPresentInPayload(): bool
    {
        return $this->replace;
    }
}
