<?php

namespace Spatie\LaravelData\Support\Validation;

class DataRules
{
    public function __construct(
        public array $rules = [],
    ) {
    }

    public static function create(): self
    {
        return new self();
    }

    public function add(
        ValidationPath $path,
        array $rules
    ): self {
        $this->rules[$path->get()] = $rules;

        return $this;
    }
}
