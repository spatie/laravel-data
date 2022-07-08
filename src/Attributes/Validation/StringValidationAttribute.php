<?php

namespace Spatie\LaravelData\Attributes\Validation;

abstract class StringValidationAttribute extends ValidationAttribute
{
    abstract public function parameters(): array;

    public static function create(string ...$parameters): static
    {
        return new static(...$parameters);
    }

    public function getRules(): array
    {
        $parameters = collect($this->parameters())->reject(fn (mixed $value) => $value === null);

        if ($parameters->isEmpty()) {
            return [$this->keyword()];
        }

        $parameters = $parameters->map(
            fn (mixed $value, int|string $key) => is_string($key) ? "{$key}={$value}" : $value
        );

        return ["{$this->keyword()}:{$parameters->join(',')}"];
    }
}
