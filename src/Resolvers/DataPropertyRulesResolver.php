<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
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
            $inferrer->handle($property, $toplevelRules, $path);
        }

        $toplevelRules = $toplevelRules->add(ArrayType::create());

        $dataRules->rules[$path] = $toplevelRules->normalize($path);

        app(DataValidationRulesResolver::class)->execute(
            $property->type->dataClass,
            $fullPayload,
            $dataRules,
            $path
        );

        return $dataRules;
    }
}
