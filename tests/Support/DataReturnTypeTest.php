<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\CanBeEscapedWhenCastToString;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Support\DataType;
use Spatie\LaravelData\Support\Factories\DataReturnTypeFactory;
use Spatie\LaravelData\Support\Types\NamedType;
use Spatie\LaravelData\Support\Types\UnionType;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class TestReturnTypeSubject
{
    public function array(): array
    {

    }

    public function collection(): Collection
    {

    }

    public function dataCollection(): DataCollection
    {

    }

    public function nullableArray(): ?array
    {

    }

    public function union(): array|Collection
    {

    }

    public function none()
    {

    }
}

it('can determine the return type from reflection', function (
    string $methodName,
    string $typeName,
    mixed $value,
    ?DataType $expected
) {
    $factory = app(DataReturnTypeFactory::class);

    $reflection = (new ReflectionMethod(\TestReturnTypeSubject::class, $methodName));

    expect($factory->build($reflection, TestReturnTypeSubject::class))->toEqual($expected);

    expect($factory->buildFromNamedType($typeName, TestReturnTypeSubject::class, false))->toEqual($expected);

    expect($factory->buildFromValue($value, TestReturnTypeSubject::class, false))->toEqual($expected);
})->with(function () {
    yield 'array' => [
        'methodName' => 'array',
        'typeName' => 'array',
        'value' => [],
        new DataType(
            type: new NamedType('array', true, [], DataTypeKind::Array, null, null, 'array', null, null),
            isNullable: false,
            isMixed: false,
            kind: DataTypeKind::Array,
        ),
    ];

    yield 'collection' => [
        'methodName' => 'collection',
        'typeName' => Collection::class,
        'value' => collect(),
        new DataType(
            type: new NamedType(Collection::class, false, [
                ArrayAccess::class,
                CanBeEscapedWhenCastToString::class,
                Enumerable::class,
                Traversable::class,
                Stringable::class,
                JsonSerializable::class,
                Jsonable::class,
                IteratorAggregate::class,
                Countable::class,
                Arrayable::class,
            ], DataTypeKind::Enumerable, null, null, Collection::class, null, null),
            isNullable: false,
            isMixed: false,
            kind: DataTypeKind::Enumerable,
        ),
    ];

    yield 'data collection' => [
        'methodName' => 'dataCollection',
        'typeName' => DataCollection::class,
        'value' => new DataCollection(SimpleData::class, []),
        new DataType(
            type: new NamedType(DataCollection::class, false, [
                Illuminate\Contracts\Support\Responsable::class,
                Spatie\LaravelData\Contracts\BaseDataCollectable::class,
                Spatie\LaravelData\Contracts\TransformableData::class,
                Spatie\LaravelData\Contracts\ResponsableData::class,
                Spatie\LaravelData\Contracts\IncludeableData::class,
                Spatie\LaravelData\Contracts\WrappableData::class,
                IteratorAggregate::class,
                Countable::class,
                ArrayAccess::class,
                Spatie\LaravelData\Contracts\ContextableData::class,
                Illuminate\Contracts\Database\Eloquent\Castable::class,
                Illuminate\Contracts\Support\Arrayable::class,
                Illuminate\Contracts\Support\Jsonable::class,
                JsonSerializable::class,
                Traversable::class,
            ], DataTypeKind::DataCollection, null, null),
            isNullable: false,
            isMixed: false,
            kind: DataTypeKind::DataCollection,
        ),
    ];
});

it('will return null when a method does not have a return type', function () {
    $factory = app(DataReturnTypeFactory::class);

    $reflection = new ReflectionMethod(\TestReturnTypeSubject::class, 'none');

    expect($factory->build($reflection, TestReturnTypeSubject::class))->toBeNull();
});

it('can handle union types', function () {
    $factory = app(DataReturnTypeFactory::class);

    $reflection = new ReflectionMethod(\TestReturnTypeSubject::class, 'union');

    expect($factory->build($reflection, TestReturnTypeSubject::class))->toEqual(
        new DataType(
            type: new UnionType([
                new NamedType(
                    Collection::class,
                    false,
                    [
                        ArrayAccess::class,
                        CanBeEscapedWhenCastToString::class,
                        Enumerable::class,
                        Traversable::class,
                        Stringable::class,
                        JsonSerializable::class,
                        Jsonable::class,
                        IteratorAggregate::class,
                        Countable::class,
                        Arrayable::class,
                    ],
                    DataTypeKind::Enumerable,
                    null,
                    null,
                    Collection::class,
                    null,
                    null,
                ),
                new NamedType(
                    'array',
                    true,
                    [],
                    DataTypeKind::Array,
                    null,
                    null,
                    'array',
                    null,
                    null,
                ),
            ]),
            isNullable: false,
            isMixed: false,
            kind: DataTypeKind::Enumerable, // in the future this should be an array ...
        ),
    );
});

it('will store return types in the factory as a caching mechanism', function () {
    $factory = app(DataReturnTypeFactory::class);

    $reflection = new ReflectionMethod(\TestReturnTypeSubject::class, 'array');

    $firstBuild = $factory->build($reflection, TestReturnTypeSubject::class);
    $secondBuild = $factory->build($reflection, TestReturnTypeSubject::class);

    expect($firstBuild)->toBe($secondBuild);
    expect(spl_object_id($firstBuild))->toBe(spl_object_id($secondBuild));
});

it('will cache nullable and non nullable return types separately', function () {
    $factory = app(DataReturnTypeFactory::class);

    $firstReflection = new ReflectionMethod(\TestReturnTypeSubject::class, 'array');
    $secondReflection = new ReflectionMethod(\TestReturnTypeSubject::class, 'array');

    $firstBuild = $factory->build($firstReflection, TestReturnTypeSubject::class);
    $secondBuild = $factory->buildFromNamedType($secondReflection, TestReturnTypeSubject::class, true);

    expect($firstBuild)->isNullable->toBeFalse();
    expect($secondBuild)->isNullable->toBeTrue();

    expect($firstBuild)->not->toBe($secondBuild);
    expect(spl_object_id($firstBuild))->not->toBe(spl_object_id($secondBuild));
});
