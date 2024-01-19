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
use Spatie\LaravelData\Support\DataReturnType;
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
}

it('can determine the return type from reflection', function (
    string $methodName,
    string $typeName,
    mixed $value,
    DataReturnType $expected
) {
    $factory = app(DataReturnTypeFactory::class);

    $reflection = (new ReflectionMethod(\TestReturnTypeSubject::class, $methodName))->getReturnType();

    expect($factory->build($reflection))->toEqual($expected);

    expect($factory->buildFromNamedType($typeName))->toEqual($expected);

    expect($factory->buildFromValue($value))->toEqual($expected);
})->with(function () {
    yield 'array' => [
        'methodName' => 'array',
        'typeName' => 'array',
        'value' => [],
        new DataReturnType(
            type: new NamedType('array', true, [], DataTypeKind::Array, null, null),
            kind: DataTypeKind::Array,
        ),
    ];

    yield 'collection' => [
        'methodName' => 'collection',
        'typeName' => Collection::class,
        'value' => collect(),
        new DataReturnType(
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
            ], DataTypeKind::Enumerable, null, null),
            kind: DataTypeKind::Enumerable,
        ),
    ];

    yield 'data collection' => [
        'methodName' => 'dataCollection',
        'typeName' => DataCollection::class,
        'value' => new DataCollection(SimpleData::class, []),
        new DataReturnType(
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
            kind: DataTypeKind::DataCollection,
        ),
    ];
});
