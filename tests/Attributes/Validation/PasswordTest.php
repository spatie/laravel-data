<?php

use Illuminate\Validation\Rules\Password as ValidationPassword;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Support\Validation\ValidationPath;

test(
    'password rule returns preconfigured password validators',
    function (callable|null $setDefaults, array $expectedConfig) {
        ValidationPassword::$defaultCallback = null;
        $setDefaults();

        [$rule] = (new Password(default: true))->getRules(ValidationPath::create());
        $clazz = new ReflectionClass($rule);

        foreach ($expectedConfig as $key => $expected) {
            $prop = $clazz->getProperty($key);
            $prop->setAccessible(true);
            $actual = $prop->getValue($rule);

            expect($actual)->toBe($expected);
        }
    }
)->with(function () {
    yield 'min length set to 42' => [
        'setDefaults' => fn () => fn () => ValidationPassword::defaults(fn () => ValidationPassword::min(42)),
        'expectedConfig' => [
            'min' => 42,
        ],
    ];

    yield 'unconfigured' => [
        'setDefaults' => fn () => fn () => ValidationPath::create(),
        'expectedConfig' => [
            'min' => 8,
        ],
    ];

    yield 'uncompromised' => [
        'setDefaults' => fn () => fn () => ValidationPassword::defaults(fn () => ValidationPassword::min(69)->uncompromised(7)),
        'expectedConfig' => [
            'min' => 69,
            'uncompromised' => true,
            'compromisedThreshold' => 7,
        ],
    ];
});
