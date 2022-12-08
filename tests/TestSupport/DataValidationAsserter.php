<?php

namespace Spatie\LaravelData\Tests\TestSupport;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\NestedRules;
use Illuminate\Validation\ValidationException;

use Illuminate\Validation\ValidationRuleParser;
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
            ->mapWithKeys(function (array|NestedRules $rules, string $key) use ($payload) {
                // Get the rules generated from the nested rules
                if ($rules instanceof NestedRules) {
                    $parser = new ValidationRuleParser($payload);
                    $result = $parser->explode([$key => $rules]);

                    return collect($result->rules)
                        ->map(fn ($rules) => array_values(Arr::sort($rules)))
                        ->sortKeys()
                        ->all();
                }
                return [$key => array_values(Arr::sort($rules))];
            })
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
