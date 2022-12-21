<?php

namespace Spatie\LaravelData\Support\Validation;

use Illuminate\Validation\NestedRules;

class NestedRulesWithAdditional extends NestedRules
{
    /**
     * Nested rules cannot be used inside an array. To add additional rules to the property
     * we need to append the rules after the nested rule validations have been compiled.
     *
     * @param  callable  $callback
     * @return void
     */
    public function __construct(
        callable $callback,
        public array $additionalRules
    ) {
        parent::__construct($callback);
    }

    public static function fromNestedRules(NestedRules $nestedRules, array $additionalRules): self
    {
        return new self($nestedRules->callback, $additionalRules);
    }

    public function compile($attribute, $value, $data = null)
    {
        /** @var \stdClass $result */
        $result = parent::compile($attribute, $value, $data);
        $result->rules = [
            ...$result->rules,
            ...$this->additionalRules,
        ];

        return $result;
    }
}
