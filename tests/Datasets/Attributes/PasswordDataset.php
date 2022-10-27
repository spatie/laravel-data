<?php

use Illuminate\Validation\Rules\Password as ValidationPassword;

dataset('preconfigured-password-validations', [
    'min length set to 42' => [
        'setDefaults' => fn () => ValidationPassword::defaults(fn () => ValidationPassword::min(42)),
        'expectedConfig' => [
            'min' => 42,
        ],
    ],

    'unconfigured' => [
        'setDefaults' => fn () => null,
        'expectedConfig' => [
            'min' => 8,
        ],
    ],

    'uncompromised' => [
        'setDefaults' => fn () => ValidationPassword::defaults(fn () => ValidationPassword::min(69)->uncompromised(7)),
        'expectedConfig' => [
            'min' => 69,
            'uncompromised' => true,
            'compromisedThreshold' => 7,
        ],
    ]
]);
