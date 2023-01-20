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
    ): void {
        if ($property->type->isOptional && Arr::has($fullPayload, $path->get()) === false) {
            return;
        }

        if ($property->type->isNullable && Arr::get($fullPayload, $path->get()) === null) {
            return;
        }

        $toplevelRules = PropertyRules::create();

        $toplevelRules->add(Present::create());
        $toplevelRules->add(ArrayType::create());

        $this->attributesRuleInferrer->handle($property, $toplevelRules, $path);

        $dataRules->add($path, $toplevelRules->normalize($path));

        $dataRules->addCollection($path, Rule::forEach(function (mixed $value, mixed $attribute) use ($fullPayload, $property) {
            if (! is_array($value)) {
                return ['array'];
            }

            $dataRules = DataRules::create();

            app(DataValidationRulesResolver::class)->execute(
                $property->type->dataClass,
                $fullPayload,
                ValidationPath::create($attribute),
                $dataRules
            );

            return collect($dataRules->rules)->keyBy(
                fn(mixed $rules, string $key) => Str::after($key, "{$attribute}.") // TODO: let's do this better
            )->all();
        }));
    }
}
