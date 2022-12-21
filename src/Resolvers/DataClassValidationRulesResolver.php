<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\NestedRules;
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

    public function execute(
        string $class,
        array $payload = [],
        bool $nullable = false,
        ?string $payloadPath = null
    ): Collection {
        $resolver = app(DataPropertyValidationRulesResolver::class);

        $class = $this->dataConfig->getDataClass($class);

        return $class
            ->properties
            ->reject(fn (DataProperty $property) => ! $property->validate)
            ->mapWithKeys(fn (DataProperty $property) => $resolver->execute($property, $payload, $nullable, $payloadPath)->all())
            ->merge($this->resolveOverwrittenRules($class, $payload, $payloadPath));
    }

    private function resolveOverwrittenRules(
        DataClass $class,
        array $payload = [],
        ?string $payloadPath = null
    ): array {
        if (! method_exists($class->name, 'rules')) {
            return [];
        }

        if ($payloadPath === null) {
            return $this->buildOverwrittenRules($class, $payload, null, $payloadPath, true);
        }

        if (Str::contains($payloadPath, '*') && class_exists(NestedRules::class)) {
            return [
                $payloadPath => Rule::forEach(
                    fn (mixed $value, string $relativePath) => $value === null
                        ? []
                        : $this->buildOverwrittenRules($class, $payload, $value, $relativePath, false)
                ),
            ];
        }

        return $this->buildOverwrittenRules(
            $class,
            $payload,
            Arr::get($payload, $payloadPath) ?? [],
            $payloadPath,
            true,
        );
    }

    private function buildOverwrittenRules(
        DataClass $class,
        mixed $payload,
        mixed $relativePayload,
        ?string $payloadPath,
        bool $prefixWithPayloadPath,
    ): array {
        $parameters = [
            'payload' => $payload,
            'path' => $payloadPath,
        ];

        if ($relativePayload !== null) {
            $parameters['relativePayload'] = $relativePayload;
        }

        $overwrittenRules = app()->call([$class->name, 'rules'], $parameters);

        return collect($overwrittenRules)
            ->map(
                fn (mixed $rules) => collect(Arr::wrap($rules))
                    ->map(fn (mixed $rule) => is_string($rule) ? explode('|', $rule) : $rule)
                    ->map(fn (mixed $rule) => $rule instanceof ValidationRule ? $rule->getRules() : $rule)
                    ->flatten()
                    ->all()
            )
            ->when(
                $payloadPath && $prefixWithPayloadPath,
                fn (Collection $collection) => $collection->keyBy(fn (mixed $rules, string $key) => "{$payloadPath}.{$key}")
            )
            ->all();
    }
}
