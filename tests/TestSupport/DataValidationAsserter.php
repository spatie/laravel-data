<?php

namespace Spatie\LaravelData\Tests\TestSupport;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Data;

use function PHPUnit\Framework\assertTrue;

/**
 * @property class-string<Data::class> $dataClass
 */
class DataValidationAsserter
{
    private readonly string $dataClass;

    public static function for(
        string|Data $dataClass
    ): self {
        return new self($dataClass);
    }

    public function __construct(
        string|Data $dataClass,
    ) {
        $this->dataClass = $dataClass instanceof Data
            ? $dataClass::class
            : $dataClass;
    }

    public function assertOk(array $payload): self
    {
        $this->dataClass::validate($payload);

        expect(true)->toBeTrue();

        return $this;
    }

    public function assertErrors(
        array $payload
    ): self {
        try {
            $this->dataClass::validate($payload);
        } catch (ValidationException $exception) {
            expect(true)->toBeTrue();

            return $this;
        }

        assertTrue(false, 'No validation errors');

        return $this;
    }

    public function assertRules(
        array $rules,
        array $payload = []
    ): self {
        $inferredRules = collect($this->dataClass::getValidationRules(payload: $payload))
            ->map(fn (array $rules) => array_values(Arr::sort($rules)))
            ->sortKeys()
            ->all();

        $rules = collect($rules)
            ->map(fn (array $rules) => array_values(Arr::sort($rules)))
            ->sortKeys()
            ->all();

        expect($inferredRules)->toEqual($rules);

        return $this;
    }
}
