
// Da
uses(TestCase::class);
tasets
dataset('preconfiguredPasswordValidationsProvider', function () {
    yield 'min length set to 42' => [
        'setDefaults' => fn () => ValidationPassword::defaults(fn () => ValidationPassword::min(42)),
        'expectedConfig' => [
            'min' => 42,
        ],
    ];

    yield 'unconfigured' => [
        'setDefaults' => fn () => null,
        'expectedConfig' => [
            'min' => 8,
        ],
    ];

    yield 'uncompromised' => [
        'setDefaults' => fn () => ValidationPassword::defaults(fn () => ValidationPassword::min(69)->uncompromised(7)),
        'expectedConfig' => [
            'min' => 69,
            'uncompromised' => true,
            'compromisedThreshold' => 7,
        ],
    ];
});
