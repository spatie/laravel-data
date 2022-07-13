<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

    public function execute(string $class, array $payload = [], bool $nullable = false): Collection
    {
        $resolver = app(DataPropertyValidationRulesResolver::class);

        $overWrittenRules = $this->resolveOverwrittenRules($class, $payload);

        return $this->dataConfig->getDataClass($class)
            ->properties
            ->reject(fn (DataProperty $property) => array_key_exists($property->name, $overWrittenRules) || ! $property->validate)
            ->mapWithKeys(fn (DataProperty $property) => $resolver->execute($property, $payload, $nullable)->all())
            ->merge($overWrittenRules);
    }

    private function resolveOverwrittenRules(string $class, array $payload = []): array
    {
        $overWrittenRules = [];

        if (method_exists($class, 'rules')) {
            $overWrittenRules = app()->call([$class, 'rules'], [
                'payload' => $payload,
            ]);
        }

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
