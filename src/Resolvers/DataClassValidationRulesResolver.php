<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\RulesMapper;
use Spatie\LaravelData\Support\Validation\ValidationRule;

class DataClassValidationRulesResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected RulesMapper $ruleAttributesResolver,
    ) {
    }

    public function execute(string $class, array $payload = [], bool $nullable = false, ?string $payloadPath = null): Collection
    {
        $resolver = app(DataPropertyValidationRulesResolver::class);

        $class = $this->dataConfig->getDataClass($class);

        return $class
            ->properties
            ->reject(fn (DataProperty $property) => ! $property->validate)
            ->mapWithKeys(fn (DataProperty $property) => $resolver->execute($property, $payload, $nullable, $payloadPath)->all())
            ->merge($this->resolveOverwrittenRules($class, $payload, $payloadPath));
    }

    private function hasOverwrittenRules(DataClass $class): bool
    {
        return method_exists($class->name, 'rules');
    }

    private function resolveOverwrittenRules(DataClass $class, array $payload = [], ?string $payloadPath = null): array
    {
        if (! $this->hasOverwrittenRules($class)) {
            return [];
        }

        // Make the payload relative to the data class
        if ($class->relativeRuleGeneration && $payloadPath) {
            // If the path contains a wildcard use nested rule generation
            if (Str::contains($payloadPath, '*')) {
                return [
                    $payloadPath => Rule::forEach(function (mixed $value, string $attribute, mixed $data = null) use ($class) {
                        if ($value === null) {
                            return [];
                        }

                        $overwrittenRules = app()->call([$class->name, 'rules'], [
                            'payload' => $value,
                            'path' => $attribute,
                        ]);

                        return $this->formatOverwrittenRules($overwrittenRules);
                    }),
                ];
            }

            $payload = Arr::get($payload, $payloadPath) ?? [];
        }

        $overwrittenRules = app()->call([$class->name, 'rules'], [
            'payload' => $payload,
            'path' => $payloadPath,
        ]);

        if ($payloadPath) {
            $overwrittenRules = collect($overwrittenRules)
                ->mapWithKeys(fn ($rules, $key) => ["{$payloadPath}.{$key}" => $rules])
                ->all();
        }

        return $this->formatOverwrittenRules($overwrittenRules);
    }

    private function formatOverwrittenRules(array $overWrittenRules): array
    {
        foreach ($overWrittenRules as $property => $rules) {
            $overWrittenRules[$property] = collect(Arr::wrap($rules))
                ->map(fn (mixed $rule) => is_string($rule) ? explode('|', $rule) : $rule)
                ->map(fn (mixed $rule) => $rule instanceof ValidationRule ? $rule->getRules() : $rule)
                ->flatten()
                ->all();
        }

        return $overWrittenRules;
    }
}
