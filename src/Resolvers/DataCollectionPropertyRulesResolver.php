<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
    public function __construct(
        protected AttributesRuleInferrer $attributesRuleInferrer,
    ) {
    }

    public function execute(
        DataProperty $property,
        string $path,
        array $fullPayload,
        DataRules $dataRules,
    ): DataRules {
        if ($property->validate === false) {
            return $dataRules;
        }

        if ($property->type->isOptional && Arr::has($fullPayload, $path) === false) {
            return $dataRules;
        }

        if ($property->type->isNullable && Arr::get($fullPayload, $path) === null) {
            return $dataRules;
        }

        $toplevelRules = RulesCollection::create();

        $toplevelRules->add(Present::create());
        $toplevelRules->add(ArrayType::create());

        $this->attributesRuleInferrer->handle($property, $toplevelRules, $path);

        $dataRules->rules[$path] = $toplevelRules->normalize($path);

        $dataRules->rules["{$path}.*"] = Rule::forEach(function (mixed $value, mixed $attribute) use ($fullPayload, $property) {
            // Attribute has full path, probably required for relative rule reference replacement

            if (! is_array($value)) {
                return ['array'];
            }

            return collect(app(DataValidationRulesResolver::class)->execute(
                $property->type->dataClass,
                $fullPayload,
                new DataRules([]),
                $attribute,
            ))->keyBy(
                fn(mixed $rules, string $key) => Str::after($key, "{$attribute}.") // TODO: let's do this better
            )->all();
        });

        return $dataRules;
    }
}
