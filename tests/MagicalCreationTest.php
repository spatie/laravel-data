<?php


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Tests\Fakes\DataWithMultipleArgumentCreationMethod;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\EnumData;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModel;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('can create data using a magical method', function () {
    $data = new class ('') extends Data {
        public function __construct(public string $string)
        {
        }

        public static function fromString(string $string): static
        {
            return new self($string);
        }

        public static function fromDto(DummyDto $dto)
        {
            return new self($dto->artist);
        }

        public static function fromArray(array $payload)
        {
            return new self($payload['string']);
        }
    };

    expect($data::from('Hello World'))->toEqual(new $data('Hello World'))
        ->and($data::from(DummyDto::rick()))->toEqual(new $data('Rick Astley'))
        ->and($data::from(DummyDto::rick()))->toEqual(new $data('Rick Astley'))
        ->and($data::from(['string' => 'Hello World']))->toEqual(new $data('Hello World'))
        ->and($data::from(DummyModelWithCasts::make(['string' => 'Hello World'])))->toEqual(new $data('Hello World'));
});

it('can magically create a data object', function () {
    $dataClass = new class ('', '') extends Data {
        public function __construct(
            public mixed $propertyA,
            public mixed $propertyB,
        ) {
        }

        public static function fromStringWithDefault(string $a, string $b = 'World')
        {
            return new self($a, $b);
        }

        public static function fromIntsWithDefault(int $a, int $b)
        {
            return new self($a, $b);
        }

        public static function fromSimpleDara(SimpleData $data)
        {
            return new self($data->string, $data->string);
        }

        public static function fromData(Data $data)
        {
            return new self('data', json_encode($data));
        }
    };

    expect($dataClass::from('Hello'))->toEqual(new $dataClass('Hello', 'World'))
        ->and($dataClass::from('Hello', 'World'))->toEqual(new $dataClass('Hello', 'World'))
        ->and($dataClass::from(42, 69))->toEqual(new $dataClass(42, 69))
        ->and($dataClass::from(SimpleData::from('Hello')))->toEqual(new $dataClass('Hello', 'Hello'))
        ->and($dataClass::from(new EnumData(DummyBackedEnum::FOO)))->toEqual(new $dataClass('data', '{"enum":"foo"}'));
});

it('can create data using a magical method with the interface of the value as type', function () {
    $data = new class ('') extends Data {
        public function __construct(public string $string)
        {
        }

        public static function fromInterface(Arrayable $arrayable)
        {
            return new self($arrayable->toArray()['string']);
        }
    };

    $interfaceable = new class () implements Arrayable {
        public function toArray()
        {
            return [
                'string' => 'Rick Astley',
            ];
        }
    };

    expect($data::from($interfaceable))->toEqual(new $data('Rick Astley'));
});

it('can create data using a magical method with the base class of the value as type', function () {
    $data = new class ('') extends Data {
        public function __construct(public string $string)
        {
        }

        public static function fromModel(Model $model)
        {
            return new self($model->string);
        }
    };

    $inherited = new DummyModel(['string' => 'Rick Astley']);

    expect($data::from($inherited))->toEqual(new $data('Rick Astley'));
});

it('can create data from a magical method with multiple parameters', function () {
    expect(DataWithMultipleArgumentCreationMethod::from('Rick Astley', 42))
        ->toEqual(new DataWithMultipleArgumentCreationMethod('Rick Astley_42'));
});

it('can inject the creation context when using a magical method', function () {
    $dataClass = new class () extends Data {
        public function __construct(
            public string $string = 'something'
        ) {
        }

        public static function fromArray(string $prefix, CreationContext $context)
        {
            return new self("{$prefix} {$context->dataClass}");
        }
    };

    expect($dataClass::from('Hi there'))
        ->string->toBe('Hi there '.$dataClass::class);
});

it('will use magic methods when creating a collection of data objects', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(public string $otherString)
        {
        }

        public static function fromSimpleData(SimpleData $simpleData): static
        {
            return new self($simpleData->string);
        }
    };

    $collection = new DataCollection($dataClass::class, [
        SimpleData::from('A'),
        SimpleData::from('B'),
    ]);

    expect($collection[0])
        ->toBeInstanceOf($dataClass::class)
        ->otherString->toEqual('A');

    expect($collection[1])
        ->toBeInstanceOf($dataClass::class)
        ->otherString->toEqual('B');
});

it('can magically collect data', function () {
    class TestSomeCustomCollection extends Collection
    {
    }

    $dataClass = new class () extends Data {
        public string $string;

        public static function fromString(string $string): self
        {
            $s = new self();

            $s->string = $string;

            return $s;
        }

        public static function collectArray(array $items): \TestSomeCustomCollection
        {
            return new \TestSomeCustomCollection($items);
        }

        public static function collectCollection(Collection $items): array
        {
            return $items->all();
        }
    };

    expect($dataClass::collect(['a', 'b', 'c']))
        ->toBeInstanceOf(\TestSomeCustomCollection::class)
        ->all()->toEqual([
            $dataClass::from('a'),
            $dataClass::from('b'),
            $dataClass::from('c'),
        ]);

    expect($dataClass::collect(collect(['a', 'b', 'c'])))
        ->toBeArray()
        ->toEqual([
            $dataClass::from('a'),
            $dataClass::from('b'),
            $dataClass::from('c'),
        ]);

    expect($dataClass::collect(new TestSomeCustomCollection(['a', 'b', 'c'])))
        ->toBeArray()
        ->toEqual([
            $dataClass::from('a'),
            $dataClass::from('b'),
            $dataClass::from('c'),
        ]);
});

it('can disable magically collecting data', function () {
    $dataClass = new class ('') extends SimpleData {
        public static function collectArray(array $items): Collection
        {
            return new Collection($items);
        }
    };

    expect($dataClass::collect(['a', 'b', 'c']))
        ->toBeInstanceOf(Collection::class)
        ->all()->toEqual([
            SimpleData::from('a'),
            SimpleData::from('b'),
            SimpleData::from('c'),
        ]);

    expect($dataClass::factory()->withoutMagicalCreation()->collect([
        ['string' => 'a'],
        ['string' => 'b'],
        ['string' => 'c'],
    ]))
        ->toBeArray()
        ->toEqual([
            new $dataClass('a'),
            new $dataClass('b'),
            new $dataClass('c'),
        ]);
});

it('can disable specific magic collecting data methods', function () {
    $dataClass = new class ('') extends SimpleData {
        public static function collectArray(array $items): Collection
        {
            return new Collection($items);
        }
    };

    expect($dataClass::collect(['a', 'b', 'c']))
        ->toBeInstanceOf(Collection::class)
        ->all()->toEqual([
            SimpleData::from('a'),
            SimpleData::from('b'),
            SimpleData::from('c'),
        ]);

    expect($dataClass::factory()->ignoreMagicalMethod('collectArray')->collect([
        ['string' => 'a'],
        ['string' => 'b'],
        ['string' => 'c'],
    ]))
        ->toBeArray()
        ->toEqual([
            new $dataClass('a'),
            new $dataClass('b'),
            new $dataClass('c'),
        ]);
});

it('can inject the creation context when collecting data with a magical method', function () {
    $dataClass = new class ('') extends SimpleData {
        public static function collectArray(array $items, CreationContext $context): array
        {
            return array_map(fn (SimpleData $data) => new SimpleData($data->string . ' ' . $context->dataClass), $items);
        }
    };

    expect($dataClass::collect(['a', 'b', 'c']))
        ->toBeArray()
        ->toEqual([
            SimpleData::from('a ' . $dataClass::class),
            SimpleData::from('b ' . $dataClass::class),
            SimpleData::from('c ' . $dataClass::class),
        ]);
});
