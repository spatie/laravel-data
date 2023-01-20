<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\RuleDenormalizer;
use Spatie\LaravelData\Support\Validation\RuleNormalizer;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\LaravelData\Support\Validation\ValidationPath;

class DataValidationRulesResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected RuleNormalizer $ruleAttributesResolver,
        protected RuleDenormalizer $ruleDenormalizer
    ) {
    }

    public function execute(
        string $class,
        array $fullPayload,
        ValidationPath $path,
        DataRules $dataRules,
    ): DataRules {
        $dataClass = $this->dataConfig->getDataClass($class);

        foreach ($dataClass->properties as $dataProperty) {
            $relativePath = $path->relative($dataProperty->inputMappedName ?? $dataProperty->name);

            if ($dataProperty->validate === false) {
                continue;
            }

            if ($dataProperty->type->isDataObject || $dataProperty->type->isDataCollectable) {
                $this->resolveDataSpecificRules(
                    $dataProperty,
                    $fullPayload,
                    $relativePath,
                    $dataRules
                );

                continue;
            }

            $rules = $this->inferRulesForDataProperty(
                $dataProperty,
                PropertyRules::create(),
                $path
            );

            $dataRules->add($relativePath, $rules);
        }

        $this->resolveOverwrittenRules($dataClass, $fullPayload, $path, $dataRules);

        return $dataRules;
    }

    protected function resolveDataSpecificRules(
        DataProperty $dataProperty,
        array $fullPayload,
        ValidationPath $path,
        DataRules $dataRules,
    ): void {
        if ($dataProperty->type->isOptional && Arr::has($fullPayload, $path->get()) === false) {
            return;
        }

        if ($dataProperty->type->isNullable && Arr::get($fullPayload, $path->get()) === null) {
            return;
        }

        if ($dataProperty->type->isDataObject) {
            $this->resolveDataObjectSpecificRules(
                $dataProperty,
                $fullPayload,
                $path,
                $dataRules
            );

            return;
        }

        if ($dataProperty->type->isDataCollectable) {
            $this->resolveDataCollectionSpecificRules(
                $dataProperty,
                $fullPayload,
                $path,
                $dataRules
            );
        }
    }

    protected function resolveDataObjectSpecificRules(
        DataProperty $dataProperty,
        array $fullPayload,
        ValidationPath $relativePath,
        DataRules $dataRules,
    ): void {
        $toplevelRules = $this->inferRulesForDataProperty(
            $dataProperty,
            PropertyRules::create(ArrayType::create()),
            $relativePath
        );

        $dataRules->add($relativePath, $toplevelRules);

        $this->execute(
            $dataProperty->type->dataClass,
            $fullPayload,
            $relativePath,
            $dataRules,
        );
    }

    protected function resolveDataCollectionSpecificRules(
        DataProperty $dataProperty,
        array $fullPayload,
        ValidationPath $relativePath,
        DataRules $dataRules,
    ): void {
        $toplevelRules = $this->inferRulesForDataProperty(
            $dataProperty,
            PropertyRules::create(Present::create(), ArrayType::create()),
            $relativePath
        );

        $dataRules->add($relativePath, $toplevelRules);

        $dataRules->addCollection($relativePath, Rule::forEach(function (mixed $value, mixed $attribute) use ($fullPayload, $dataProperty) {
            if (! is_array($value)) {
                return ['array'];
            }

            $dataRules = DataRules::create();

            app(DataValidationRulesResolver::class)->execute(
                $dataProperty->type->dataClass,
                $fullPayload,
                ValidationPath::create($attribute),
                $dataRules
            );

            return collect($dataRules->rules)->keyBy(
                fn (mixed $rules, string $key) => Str::after($key, "{$attribute}.") // TODO: let's do this better
            )->all();
        }));
    }

    protected function resolveOverwrittenRules(
        DataClass $class,
        array $fullPayload,
        ValidationPath $path,
        DataRules $dataRules,
    ): void {
        if (! method_exists($class->name, 'rules')) {
            return;
        }

        $validationContext = new ValidationContext(
            $path->isRoot() ? $fullPayload : Arr::get($fullPayload, $path->get()),
            $fullPayload,
            $path
        );

        $overwrittenRules = app()->call([$class->name, 'rules'], ['context' => $validationContext]);

        foreach ($overwrittenRules as $key => $rules) {
            $dataRules->add(
                $path->relative($key),
                collect(Arr::wrap($rules))->map(fn (mixed $rule) => $this->ruleDenormalizer->execute($rule, $path))->flatten()->all()
            );
        }
    }

    protected function inferRulesForDataProperty(
        DataProperty $property,
        PropertyRules $rules,
        ValidationPath $path,
    ): array {
        foreach ($this->dataConfig->getRuleInferrers() as $inferrer) {
            $inferrer->handle($property, $rules, $path);
        }

        return $this->ruleDenormalizer->execute(
            $rules->all(),
            $path
        );
    }
}
