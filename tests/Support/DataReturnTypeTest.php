<?php

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\CanBeEscapedWhenCastToString;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\ContextableData;
use Spatie\LaravelData\Contracts\DataCollectable;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Contracts\ResponsableData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\WrappableData;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Support\DataType;
use Spatie\LaravelData\Support\Factories\DataReturnTypeFactory;
use Spatie\LaravelData\Support\Types\NamedType;
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
}

it('can determine the return type from reflection', function (
    string $methodName,
    string $typeName,
    mixed $value,
    DataType $expected
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
            type: new NamedType('array', true, [], DataTypeKind::DataArray, null, null),
            isNullable: false,
            isMixed: false,
            kind: DataTypeKind::DataArray,
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
            ], DataTypeKind::DataEnumerable, null, null),
            isNullable: false,
            isMixed: false,
            kind: DataTypeKind::DataEnumerable,
        ),
    ];

    yield 'data collection' => [
        'methodName' => 'dataCollection',
        'typeName' => DataCollection::class,
        'value' => new DataCollection(SimpleData::class, []),
        new DataType(
            type: new NamedType(DataCollection::class, false, [
                DataCollectable::class,
                ArrayAccess::class,
                Traversable::class,
                ContextableData::class,
                Castable::class,
                Arrayable::class,
                Jsonable::class,
                JsonSerializable::class,
                Countable::class,
                IteratorAggregate::class,
                WrappableData::class,
                IncludeableData::class,
                TransformableData::class,
                ResponsableData::class,
                BaseDataCollectable::class,
                Responsable::class,

            ], DataTypeKind::DataCollection, null, null),
            isNullable: false,
            isMixed: false,
            kind: DataTypeKind::DataCollection,
        ),
    ];
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