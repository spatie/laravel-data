<?php

namespace Spatie\LaravelData\Tests;

use Attribute;
use Spatie\LaravelData\Attributes\FromData\FromDataAttribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

it('can fill data properties', function () {
    FakeFromData::$value = 'foo';
    $data = new class () extends Data {
        #[FakeFromData]
        public string $foo;
    };
    $data = $data::from(null);
    expect($data->foo)->toBe('foo');
});

it('can skip filling when it returns null', function () {
    FakeFromData::$value = null;
    $data = new class () extends Data {
        #[FakeFromData]
        public string $foo = 'foo';
    };
    $data = $data::from(null);
    expect($data->foo)->toBe('foo');
});

#[Attribute(Attribute::TARGET_PROPERTY)]
class FakeFromData implements FromDataAttribute
{
    public static mixed $value = null;

    public function resolve(
        DataProperty $dataProperty,
        mixed $payload,
        array $properties,
        CreationContext $creationContext
    ): mixed {
        return self::$value;
    }
}
