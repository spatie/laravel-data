<?php

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\Annotations\CollectionAnnotation;
use Spatie\LaravelData\Support\Annotations\CollectionAnnotationReader;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it(
    'can get the data class for a collection by annotation',
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

class NonDataCollectionWithoutExtends extends Collection
{
}

/**
 * @extends \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum
 */
class NonCollectionWithTemplate
{
}
