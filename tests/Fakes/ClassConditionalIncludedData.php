<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class ClassConditionalIncludedData extends Data
{
    public function __construct(
        protected bool $include,
        public Lazy|string $string,
    ) {
    }

    protected function includes(): array
    {
        return [
            'string' => $this->include,
        ];
    }

    public static function fromBool(bool $include): self
    {
        return new self($include, Lazy::create(fn () => 'Hello World'));
    }
}
