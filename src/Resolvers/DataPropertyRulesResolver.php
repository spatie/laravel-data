<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Resolvers\DataValidationRulesResolver;
use Spatie\LaravelData\Resolvers\DataClassValidationRulesResolver;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\RulesCollection;

class DataPropertyRulesResolver
{
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

        if (($property->type->isNullable || $property->type->isMixed) && Arr::get($fullPayload, $path) === null) {
            return $dataRules;
        }

        $toplevelRules = RulesCollection::create();

        foreach (app(DataConfig::class)->getRuleInferrers() as $inferrer) {
            $inferrer->handle($property, $toplevelRules);
        }

        $toplevelRules = $toplevelRules->add(ArrayType::create());

        $dataRules->rules[$path] = $toplevelRules->normalize();

        app(DataValidationRulesResolver::class)->execute(
            $property->type->dataClass,
            $fullPayload,
            $dataRules,
            $path
        );

        return $dataRules;
    }
}
