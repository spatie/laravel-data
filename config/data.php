<?php

return [
    /*
     * The date format to be used when converting a DateTimeInterface from and to json
     */
    'date_format' => DATE_ATOM,

    /*
     * Transformers will take properties within your data objects and transform
     * them to types that can be JSON encoded.
     */
    'transformers' => [
        \Spatie\LaravelData\Transformers\DateTransformer::class,
        \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
    ],

    /*
     * Global casts will automatically transform values in arrays to data object properties
     */
    'casts' => [
        DateTimeInterface::class => Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
    ],

    /*
     * Validation Rules that will automatically be added when a data object is resolved
     * from a request
     */
    'rule_inferrers' => [
        Spatie\LaravelData\RuleInferrers\NullableRuleInferrer::class,
        Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer::class,
        Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer::class,
        Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer::class,
    ],
];
