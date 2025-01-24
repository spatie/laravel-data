<?php

use Illuminate\Support\Collection;
use phpDocumentor\Reflection\TypeResolver;
use Spatie\LaravelData\Resolvers\ContextResolver;
use Spatie\LaravelData\Support\Annotations\CollectionAnnotation;
use Spatie\LaravelData\Support\Annotations\CollectionAnnotationReader;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

beforeEach(function () {
    CollectionAnnotationReader::clearCache();
});

it(
    'verifies the correct CollectionAnnotation is returned for a given class',
    function (string $className, ?CollectionAnnotation $expected) {
        $annotations = app(CollectionAnnotationReader::class)->getForClass($className);

        expect($annotations)->toEqual($expected);
    }
)->with(function () {
    yield DataCollectionWithTemplate::class => [
         DataCollectionWithTemplate::class, // className
         new CollectionAnnotation(type: SimpleData::class, isData: true), // expected
    ];

    yield DataCollectionWithoutTemplate::class => [
         DataCollectionWithoutTemplate::class, // className
         new CollectionAnnotation(type: SimpleData::class, isData: true), // expected
    ];

    yield DataCollectionWithCombinationType::class => [
         DataCollectionWithCombinationType::class, // className
         new CollectionAnnotation(type: SimpleData::class, isData: true), // expected
    ];

    yield DataCollectionWithIntegerKey::class => [
         DataCollectionWithIntegerKey::class, // className
         new CollectionAnnotation(type: SimpleData::class, isData: true, keyType: 'int'), // expected
    ];

    yield DataCollectionWithCombinationKey::class => [
         DataCollectionWithCombinationKey::class, // className
         new CollectionAnnotation(type: SimpleData::class, isData: true, keyType: 'int'), // expected
    ];

    yield DataCollectionWithoutKey::class => [
         DataCollectionWithoutKey::class, // className
         new CollectionAnnotation(type: SimpleData::class, isData: true), // expected
    ];

    yield NonDataCollectionWithTemplate::class => [
         NonDataCollectionWithTemplate::class, // className
         new CollectionAnnotation(type: DummyBackedEnum::class, isData: false), // expected
    ];

    yield NonDataCollectionWithoutTemplate::class => [
         NonDataCollectionWithoutTemplate::class, // className
         new CollectionAnnotation(type: DummyBackedEnum::class, isData: false), // expected
    ];

    yield NonDataCollectionWithCombinationType::class => [
         NonDataCollectionWithCombinationType::class, // className
         new CollectionAnnotation(type: DummyBackedEnum::class, isData: false), // expected
    ];

    yield NonDataCollectionWithIntegerKey::class => [
         NonDataCollectionWithIntegerKey::class, // className
         new CollectionAnnotation(type: DummyBackedEnum::class, isData: false, keyType: 'int'), // expected
    ];

    yield NonDataCollectionWithCombinationKey::class => [
         NonDataCollectionWithCombinationKey::class, // className
         new CollectionAnnotation(type: DummyBackedEnum::class, isData: false, keyType: 'int'), // expected
    ];

    yield NonDataCollectionWithoutKey::class => [
         NonDataCollectionWithoutKey::class, // className
         new CollectionAnnotation(type: DummyBackedEnum::class, isData: false), // expected
    ];

    yield CollectionWhoImplementsIterator::class => [
         CollectionWhoImplementsIterator::class, // className
         new CollectionAnnotation(type: DummyBackedEnum::class, isData: false), // expected
    ];

    yield CollectionWhoImplementsIteratorAggregate::class => [
         CollectionWhoImplementsIteratorAggregate::class, // className
         new CollectionAnnotation(type: DummyBackedEnum::class, isData: false), // expected
    ];

    yield CollectionWhoImplementsNothing::class => [
         CollectionWhoImplementsNothing::class, // className
         null, // expected
    ];

    yield CollectionWithoutDocBlock::class => [
         CollectionWithoutDocBlock::class, // className
         null, // expected
    ];

    yield CollectionWithoutType::class => [
         CollectionWithoutType::class, // className
         null, // expected
    ];
});

it('can caches the result', function (string $className) {

    $collectionAnnotationReader = Mockery::spy(CollectionAnnotationReader::class, [
        app(ContextResolver::class),
        app(TypeResolver::class),
    ])->makePartial();

    $collectionAnnotation = $collectionAnnotationReader->getForClass($className);

    $cachedCollectionAnnotation = $collectionAnnotationReader->getForClass($className);

    expect($cachedCollectionAnnotation)->toBe($collectionAnnotation);
})->with([
    [CollectionWhoImplementsNothing::class],
    [CollectionWithoutDocBlock::class],
    [DataCollectionWithTemplate::class],
]);

/**
 * @template TKey of array-key
 * @template TData of \Spatie\LaravelData\Tests\Fakes\SimpleData
 *
 * @extends \Illuminate\Support\Collection<TKey, TData>
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

/**
 * @extends \Illuminate\Support\Collection<array-key, \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum>
 */
class CollectionWhoImplementsIterator implements Iterator
{
    public function current(): mixed
    {
    }
    public function next(): void
    {
    }
    public function key(): mixed
    {
    }
    public function valid(): bool
    {
        return true;
    }
    public function rewind(): void
    {
    }
}

/**
 * @extends \Illuminate\Support\Collection<array-key, \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum>
 */
class CollectionWhoImplementsIteratorAggregate implements IteratorAggregate
{
    public function getIterator(): Traversable
    {
        return $this;
    }
}

/**
 * @extends \Illuminate\Support\Collection<array-key, \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum>
 */
class CollectionWhoImplementsNothing
{
}

class CollectionWithoutDocBlock extends Collection
{
}

/**
 * @extends \Illuminate\Support\Collection
 */
class CollectionWithoutType extends Collection
{
}
