<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\RulesMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\LaravelData\Support\Validation\ValidationPath;
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
        array $fullPayload,
        ValidationPath $path,
        DataRules $dataRules,
    ): array {
        $dataClass = $this->dataConfig->getDataClass($class);

        foreach ($dataClass->properties as $dataProperty) {
            $relativePath = $path->relative($dataProperty->inputMappedName ?? $dataProperty->name);

            if ($dataProperty->validate === false) {
                continue;
            }

            if ($dataProperty->type->isDataObject) {
                $this->dataPropertyRulesResolver->execute(
                    $dataProperty,
                    $fullPayload,
                    $relativePath,
                    $dataRules
                );

                continue;
            }

            if ($dataProperty->type->isDataCollectable) {
                $this->dataCollectionPropertyRulesResolver->execute(
                    $dataProperty,
                    $fullPayload,
                    $relativePath,
                    $dataRules
                );

                continue;
            }

            $rules = new PropertyRules();

            foreach ($this->dataConfig->getRuleInferrers() as $inferrer) {
                $rules = $inferrer->handle($dataProperty, $rules, $path);
            }

            $dataRules->add($relativePath, $rules->normalize($path));
        }

        $dataRules->rules = array_merge(
            $dataRules->rules,
            $this->resolveOverwrittenRules($dataClass, $fullPayload, $path)
        );

        return $dataRules->rules;
    }

    private function resolveOverwrittenRules(
        DataClass $class,
        array $fullPayload,
        ValidationPath $path
    ): array {
        if (! method_exists($class->name, 'rules')) {
            return [];
        }

        $fullPayload = new ValidationContext(
            $path->isRoot() ? $fullPayload : Arr::get($fullPayload, $path->get()),
            $fullPayload,
            $path
        );

        $overwrittenRules = app()->call([$class->name, 'rules'], ['context' => $fullPayload]);

        return collect($overwrittenRules)
            ->mapWithKeys(function (mixed $rules, string $key) use ($fullPayload, $path) {
                $overwrittenRules = collect(Arr::wrap($rules))
                    ->map(fn(mixed $rule) => is_string($rule) ? explode('|', $rule) : $rule)
                    ->map(fn(mixed $rule) => $rule instanceof ValidationRule ? $rule->getRules($path) : $rule)
                    ->flatten()
                    ->all();

                return [$path->relative($key)->get() => $overwrittenRules];
            })
            ->all();
    }
}
