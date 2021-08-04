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
    'auto_rules' => [
        Spatie\LaravelData\AutoRules\NullableAutoRule::class,
        Spatie\LaravelData\AutoRules\RequiredAutoRule::class,
        Spatie\LaravelData\AutoRules\BuiltInTypesAutoRule::class,
        Spatie\LaravelData\AutoRules\AttributesAutoRule::class,
    ],
];
