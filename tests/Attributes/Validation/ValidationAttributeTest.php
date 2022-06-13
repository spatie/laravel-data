
// Da
uses(TestCase::class);
tasets
dataset('values', function () {
    yield [
        'input' => 'Hello world',
        'output' => 'Hello world',
    ];

    yield [
        'input' => 42,
        'output' => '42',
    ];

    yield [
        'input' => 3.14,
        'output' => '3.14',
    ];

    yield [
        'input' => true,
        'output' => 'true',
    ];

    yield [
        'input' => false,
        'output' => 'false',
    ];

    yield [
        'input' => ['a', 'b', 'c'],
        'output' => 'a,b,c',
    ];

    yield [
        'input' => CarbonImmutable::create(2020, 05, 16, 0, 0, 0, new DateTimeZone('Europe/Brussels')),
        'output' => '2020-05-16T00:00:00+02:00',
    ];
});
