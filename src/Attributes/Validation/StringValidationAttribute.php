<?php

namespace Spatie\LaravelData\Attributes\Validation;

abstract class StringValidationAttribute extends ValidationAttribute
{
    abstract public function parameters(?string $path): array;

    public static function create(string ...$parameters): static
    {
        return new static(...$parameters);
    }

    public function getRules(?string $path): array
    {
        $parameters = collect($this->parameters($path))->reject(fn (mixed $value) => $value === null);

        if ($parameters->isEmpty()) {
            return [$this->keyword()];
        }

        $parameters = $parameters->map(
            fn (mixed $value, int|string $key) => is_string($key) ? "{$key}={$value}" : $value
        );

        return ["{$this->keyword()}:{$parameters->join(',')}"];
    }
}
