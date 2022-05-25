<?php

namespace Spatie\LaravelData\Attributes\Validation;

abstract class StringValidationAttribute extends ValidationAttribute
{
    abstract public static function keyword(): string;

    abstract public function parameters(): array;

    public static function create(string ...$parameters): static
    {
        return new static(...$parameters);
    }

    public function getRules(): array
    {
        $parameters = $this->parameters();

        if (empty($parameters)) {
            return [$this->keyword()];
        }

        $parameters = collect($this->parameters())
            ->map(fn (mixed $value, int|string $key) => is_string($key) ? "{$key}={$value}" : $value)
            ->join(',');

        return ["{$this->keyword()}:{$parameters}"];
    }
}
