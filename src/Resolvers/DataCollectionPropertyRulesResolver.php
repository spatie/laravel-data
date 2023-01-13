<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Resolvers\DataValidationRulesResolver;
use Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\RulesCollection;

class DataCollectionPropertyRulesResolver
{
    public function execute(
        DataProperty $property,
        string $path,
        array $payload,
        DataRules $dataRules,
    ): DataRules {
        if ($property->validate === false) {
            return $dataRules;
        }

        if ($property->type->isOptional && Arr::has($payload, $path) === false) {
            return $dataRules;
        }

        if ($property->type->isNullable && Arr::get($payload, $path) === null) {
            return $dataRules;
        }

        $toplevelRules = RulesCollection::create();

        $toplevelRules->add(Present::create());
        $toplevelRules->add(ArrayType::create());

        app(AttributesRuleInferrer::class)->handle($property, $toplevelRules);

        $dataRules->rules[$path] = $toplevelRules->normalize();

        $dataRules->rules["{$path}.*"] = Rule::forEach(function (mixed $value, mixed $attribute) use ($property) {
            // Attribute has full path, probably required for relative rule reference replacement

            if (! is_array($value)) {
                return ['array'];
            }

            return app(DataValidationRulesResolver::class)->execute(
                $property->type->dataClass,
                new DataRules([]),
                $value ?? [],
            )->rules;
        });

        return $dataRules;
    }
}
