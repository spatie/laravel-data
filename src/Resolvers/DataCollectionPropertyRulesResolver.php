<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\ValidationPath;

class DataCollectionPropertyRulesResolver
{
    public function __construct(
        protected AttributesRuleInferrer $attributesRuleInferrer,
    ) {
    }

    public function execute(
        DataProperty $property,
        array $fullPayload,
        ValidationPath $path,
        DataRules $dataRules,
    ): DataRules {
        if ($property->type->isOptional && Arr::has($fullPayload, $path->get()) === false) {
            return $dataRules;
        }

        if ($property->type->isNullable && Arr::get($fullPayload, $path->get()) === null) {
            return $dataRules;
        }

        $toplevelRules = PropertyRules::create();

        $toplevelRules->add(Present::create());
        $toplevelRules->add(ArrayType::create());

        $this->attributesRuleInferrer->handle($property, $toplevelRules, $path);

        $dataRules->add($path, $toplevelRules->normalize($path));

        $dataRules->rules["{$path->get()}.*"] = Rule::forEach(function (mixed $value, mixed $attribute) use ($fullPayload, $property) {
            // Attribute has full path, probably required for relative rule reference replacement

            if (! is_array($value)) {
                return ['array'];
            }

            return collect(app(DataValidationRulesResolver::class)->execute(
                $property->type->dataClass,
                $fullPayload,
                ValidationPath::create($attribute),
                DataRules::create()
            ))->keyBy(
                fn (mixed $rules, string $key) => Str::after($key, "{$attribute}.") // TODO: let's do this better
            )->all();
        });

        return $dataRules;
    }
}
