<?php

namespace Spatie\LaravelData\Support\Validation;

use Illuminate\Validation\NestedRules;

class DataRules
{
    /**
     * @param array<array|\Spatie\LaravelData\Support\Validation\PropertyRules|NestedRules> $rules
     */
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

    public function addCollection(
        ValidationPath $path,
        NestedRules $rules
    ): self {
        $this->rules["{$path->get()}.*"] = $rules;

        return $this;
    }
}
