<?php

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\Annotations\CollectionAnnotation;
use Spatie\LaravelData\Support\Annotations\CollectionAnnotationReader;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it(
    'verifies the correct CollectionAnnotation is returned for a given class',
    function (string $className, ?CollectionAnnotation $expected) {
        $annotations = app(CollectionAnnotationReader::class)->getForClass(new ReflectionClass($className));

        expect($annotations)->toEqual($expected);
    }
)->with(function () {
    yield DataCollectionWithTemplate::class => [
        'className' => DataCollectionWithTemplate::class,
        'expected' => new CollectionAnnotation(type: SimpleData::class, isData: true),
    ];

    yield DataCollectionWithoutTemplate::class => [
        'className' => DataCollectionWithoutTemplate::class,
        'expected' => new CollectionAnnotation(type: SimpleData::class, isData: true),
    ];

    yield DataCollectionWithCombinationType::class => [
        'className' => DataCollectionWithCombinationType::class,
        'expected' => new CollectionAnnotation(type: SimpleData::class, isData: true),
    ];

    yield DataCollectionWithIntegerKey::class => [
        'className' => DataCollectionWithIntegerKey::class,
        'expected' => new CollectionAnnotation(type: SimpleData::class, isData: true, keyType: 'int'),
    ];

    yield DataCollectionWithCombinationKey::class => [
        'className' => DataCollectionWithCombinationKey::class,
        'expected' => new CollectionAnnotation(type: SimpleData::class, isData: true, keyType: 'int'),
    ];

    yield DataCollectionWithoutKey::class => [
        'className' => DataCollectionWithoutKey::class,
        'expected' => new CollectionAnnotation(type: SimpleData::class, isData: true),
    ];

    yield DataCollectionWithoutExtends::class => [
        'className' => DataCollectionWithoutExtends::class,
        'expected' => null,
    ];

    yield NonDataCollectionWithTemplate::class => [
        'className' => NonDataCollectionWithTemplate::class,
        'expected' => new CollectionAnnotation(type: DummyBackedEnum::class, isData: false),
    ];

    yield NonDataCollectionWithoutTemplate::class => [
        'className' => NonDataCollectionWithoutTemplate::class,
        'expected' => new CollectionAnnotation(type: DummyBackedEnum::class, isData: false),
    ];

    yield NonDataCollectionWithCombinationType::class => [
        'className' => NonDataCollectionWithCombinationType::class,
        'expected' => new CollectionAnnotation(type: DummyBackedEnum::class, isData: false),
    ];

    yield NonDataCollectionWithIntegerKey::class => [
        'className' => NonDataCollectionWithIntegerKey::class,
        'expected' => new CollectionAnnotation(type: DummyBackedEnum::class, isData: false, keyType: 'int'),
    ];

    yield NonDataCollectionWithCombinationKey::class => [
        'className' => NonDataCollectionWithCombinationKey::class,
        'expected' => new CollectionAnnotation(type: DummyBackedEnum::class, isData: false, keyType: 'int'),
    ];

    yield NonDataCollectionWithoutKey::class => [
        'className' => NonDataCollectionWithoutKey::class,
        'expected' => new CollectionAnnotation(type: DummyBackedEnum::class, isData: false),
    ];

    yield NonDataCollectionWithoutExtends::class => [
        'className' => NonDataCollectionWithoutExtends::class,
        'expected' => null,
    ];

    yield NonCollectionWithTemplate::class => [
        'className' => NonCollectionWithTemplate::class,
        'expected' => null,
    ];
});


/**
 * @template TKey of array-key
 * @template TValue of \Spatie\LaravelData\Tests\Fakes\SimpleData
 *
 * @extends \Illuminate\Support\Collection<TKey, TValue>
 */
class DataCollectionWithTemplate extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection<array-key, \Spatie\LaravelData\Tests\Fakes\SimpleData>
 */
class DataCollectionWithoutTemplate extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection<array-key, \Spatie\LaravelData\Tests\Fakes\SimpleData|\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum>
 */
class DataCollectionWithCombinationType extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection<int, \Spatie\LaravelData\Tests\Fakes\SimpleData>
 */
class DataCollectionWithIntegerKey extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection<int|string, \Spatie\LaravelData\Tests\Fakes\SimpleData>
 */
class DataCollectionWithCombinationKey extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection<\Spatie\LaravelData\Tests\Fakes\SimpleData>
 */
class DataCollectionWithoutKey extends Collection
{
}

class DataCollectionWithoutExtends extends Collection
{
}

/**
 * @template TKey of array-key
 * @template TValue of \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum
 *
 * @extends \Illuminate\Support\Collection<TKey, TValue>
 */
class NonDataCollectionWithTemplate extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection<array-key, \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum>
 */
class NonDataCollectionWithoutTemplate extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection<array-key, \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum|\Spatie\LaravelData\Tests\Fakes\SimpleData>
 */
class NonDataCollectionWithCombinationType extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection<int, \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum>
 */
class NonDataCollectionWithIntegerKey extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection<int|string, \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum>
 */
class NonDataCollectionWithCombinationKey extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection<\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum>
 */
class NonDataCollectionWithoutKey extends Collection
{
}

class NonDataCollectionWithoutExtends extends Collection
{
}

/**
 * @extends \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum
 */
class NonCollectionWithTemplate
{
}
