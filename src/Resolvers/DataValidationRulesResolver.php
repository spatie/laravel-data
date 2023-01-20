<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\NestedRules;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Resolvers\DataCollectionPropertyRulesResolver;
use Spatie\LaravelData\Resolvers\DataPropertyRulesResolver;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\LaravelData\Support\Validation\RulesCollection;
use Spatie\LaravelData\Support\Validation\RulesMapper;
use Spatie\LaravelData\Support\Validation\ValidationRule;

class DataValidationRulesResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected RulesMapper $ruleAttributesResolver,
        protected DataPropertyRulesResolver $dataPropertyRulesResolver,
        protected DataCollectionPropertyRulesResolver $dataCollectionPropertyRulesResolver,
    ) {
    }

    public function execute(
        string $class,
        array $fullPayload = [],
        ?DataRules $dataRules = null,
        ?string $path = null,
    ): array {
        $dataClass = $this->dataConfig->getDataClass($class);

        $dataRules ??= new DataRules();

        foreach ($dataClass->properties as $dataProperty) {
            $relativePath = $this->resolveRulePath(
                $path,
                $dataProperty->inputMappedName ?? $dataProperty->name
            );

            if ($dataProperty->validate === false) {
                continue;
            }

            if ($dataProperty->type->isDataObject) {
                $this->dataPropertyRulesResolver->execute(
                    $dataProperty,
                    $relativePath,
                    $fullPayload,
                    $dataRules
                );

                continue;
            }

            if ($dataProperty->type->isDataCollectable) {
                $this->dataCollectionPropertyRulesResolver->execute(
                    $dataProperty,
                    $relativePath,
                    $fullPayload,
                    $dataRules
                );

                continue;
            }

            $rules = new RulesCollection();

            foreach ($this->dataConfig->getRuleInferrers() as $inferrer) {
                $rules = $inferrer->handle($dataProperty, $rules);
            }

            $dataRules->rules[$relativePath] = $rules->normalize();
        }

        $dataRules->rules = array_merge(
            $dataRules->rules,
            $this->resolveOverwrittenRules($dataClass, $fullPayload, $path)
        );

        return $dataRules->rules;
    }

    private function resolveOverwrittenRules(
        DataClass $class,
        array $fullPayload = [],
        ?string $payloadPath = null
    ): array {
        if (! method_exists($class->name, 'rules')) {
            return [];
        }

        $fullPayload = new ValidationContext(
            $payloadPath ? Arr::get($fullPayload, $payloadPath) : $fullPayload,
            $fullPayload,
            $payloadPath
        );

        $overwrittenRules = app()->call([$class->name, 'rules'], ['context' => $fullPayload]);

        return collect($overwrittenRules)
            ->map(
                fn(mixed $rules) => collect(Arr::wrap($rules))
                    ->map(fn(mixed $rule) => is_string($rule) ? explode('|', $rule) : $rule)
                    ->map(fn(mixed $rule) => $rule instanceof ValidationRule ? $rule->getRules() : $rule)
                    ->flatten()
                    ->all()
            )
            ->keyBy(fn(mixed $rules, string $key) => $payloadPath === null ? $key : "{$payloadPath}.{$key}")
            ->all();
    }

    private function resolveRulePath(?string $payloadPath, string $propertyName): string
    {
        return $payloadPath ? "{$payloadPath}.{$propertyName}" : $propertyName;
    }
}
