<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\ValidationPath;

class DataPropertyRulesResolver
{
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

        foreach (app(DataConfig::class)->getRuleInferrers() as $inferrer) {
            $inferrer->handle($property, $toplevelRules, $path);
        }

        $toplevelRules = $toplevelRules->add(ArrayType::create());

        $dataRules->add($path, $toplevelRules->normalize($path));

        app(DataValidationRulesResolver::class)->execute(
            $property->type->dataClass,
            $fullPayload,
            $path,
            $dataRules,
        );
    }
}
