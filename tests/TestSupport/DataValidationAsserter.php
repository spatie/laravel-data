<?php

namespace Spatie\LaravelData\Tests\TestSupport;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

use Spatie\LaravelData\Support\Validation\NestedRulesWithAdditional;
use function PHPUnit\Framework\assertTrue;

use Spatie\LaravelData\Data;

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
        array $payload,
        ?array $errors = null
    ): self {
        try {
            $this->dataClass::validate($payload);
        } catch (ValidationException $exception) {
            expect(true)->toBeTrue();

            if ($errors) {
                expect($exception->errors())->toBe($errors);
            }

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
            ->map(function (array|NestedRulesWithAdditional $rules) {
                // Replace class with class name
                if ($rules instanceof NestedRulesWithAdditional) {
                    return [
                        NestedRulesWithAdditional::class => collect($rules->additionalRules)
                            ->map(fn (array $rules) => array_values(Arr::sort($rules)))
                            ->sortKeys()
                            ->all(),
                    ];
                }
                return array_values(Arr::sort($rules));
            })
            ->sortKeys()
            ->all();

        $rules = collect($rules)
            ->map(function (array $rules) {
                if (Arr::has($rules, NestedRulesWithAdditional::class)) {
                    return [
                        NestedRulesWithAdditional::class => collect($rules[NestedRulesWithAdditional::class])
                            ->map(fn (array $rules) => array_values(Arr::sort($rules)))
                            ->sortKeys()
                            ->all(),
                    ];
                }
                return array_values(Arr::sort($rules));
            })
            ->sortKeys()
            ->all();

        expect($inferredRules)->toEqual($rules);

        return $this;
    }
}
