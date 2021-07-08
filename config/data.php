<?php

return [
    /*
     * Transformers will take properties within your data objects and transform
     * them to types that can be JSON encoded.
     */
    'transformers' => [
        \Spatie\LaravelData\Transformers\DateTransformer::class,
        \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
    ],

    'casts' => [
        \Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
    ]
];
