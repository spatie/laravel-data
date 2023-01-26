<?php

namespace Spatie\LaravelData\RuleInferrers;

use BackedEnum;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\RequiringRule;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class BuiltInTypesRuleInferrer implements RuleInferrer
{
    public function handle(
        DataProperty $property,
        PropertyRules $rules,
        ValidationContext $context,
    ): PropertyRules {
        if ($property->type->acceptsType('int')) {
            $rules->add(new Numeric());
        }

        if ($property->type->acceptsType('string')) {
            $rules->add(new StringType());
        }

        if ($property->type->acceptsType('bool')) {
            $rules->removeType(RequiringRule::class);

            $rules->add(new BooleanType());
        }

        if ($property->type->acceptsType('float')) {
            $rules->add(new Numeric());
        }

        if ($property->type->acceptsType('array')) {
            $rules->add(new ArrayType());
        }

        if ($enumClass = $property->type->findAcceptedTypeForBaseType(BackedEnum::class)) {
            $rules->add(new Enum($enumClass));
        }

        return $rules;
    }
}
