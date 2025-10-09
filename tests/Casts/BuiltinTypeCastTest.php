<?php

use Spatie\LaravelData\Casts\BuiltinTypeCast;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;

it('can cast string "true" to bool', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            'true',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeTrue();
});

it('can cast string "false" to bool', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            'false',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeFalse();
});

it('can cast string "TRUE" (uppercase) to bool', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            'TRUE',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeTrue();
});

it('can cast string "FALSE" (uppercase) to bool', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            'FALSE',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeFalse();
});

it('can cast mixed case string "TrUe" to bool', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            'TrUe',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeTrue();
});

it('can cast integer 1 to bool true', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            1,
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeTrue();
});

it('can cast integer 0 to bool false', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            0,
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeFalse();
});

it('can cast non-empty string to bool true', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            'some text',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeTrue();
});

it('can cast empty string to bool false', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            '',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeFalse();
});

it('can cast string "0" to bool false', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            '0',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeFalse();
});

it('can cast string "1" to bool true', function () {
    $class = new class () {
        public bool $value;
    };

    $caster = new BuiltinTypeCast('bool');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            '1',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeTrue();
});

it('can cast int types', function () {
    $class = new class () {
        public int $value;
    };

    $caster = new BuiltinTypeCast('int');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            '42',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBe(42);
});

it('can cast float types', function () {
    $class = new class () {
        public float $value;
    };

    $caster = new BuiltinTypeCast('float');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            '42.5',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBe(42.5);
});

it('can cast string types', function () {
    $class = new class () {
        public string $value;
    };

    $caster = new BuiltinTypeCast('string');

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            42,
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBe('42');
});

it('can cast array types', function () {
    $class = new class () {
        public array $value;
    };

    $caster = new BuiltinTypeCast('array');

    $object = (object) ['key' => 'value'];

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            $object,
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBe(['key' => 'value']);
});
