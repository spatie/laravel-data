<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithCastable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\BaseData;
use Spatie\LaravelData\Concerns\ContextableData;
use Spatie\LaravelData\Concerns\EmptyData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\ValidateableData;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Dto;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Exceptions\CannotSetComputedValue;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resource;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Tests\Fakes\Castables\SimpleCastable;
use Spatie\LaravelData\Tests\Fakes\Casts\ConfidentialDataCast;
use Spatie\LaravelData\Tests\Fakes\Casts\ConfidentialDataCollectionCast;
use Spatie\LaravelData\Tests\Fakes\Casts\ContextAwareCast;
use Spatie\LaravelData\Tests\Fakes\Casts\StringToUpperCast;
use Spatie\LaravelData\Tests\Fakes\CircData;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\EnumData;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModel;
use Spatie\LaravelData\Tests\Fakes\MultiData;
use Spatie\LaravelData\Tests\Fakes\MultiNestedData;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithoutConstructor;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithWrap;
use Spatie\LaravelData\Tests\Fakes\SimpleDto;
use Spatie\LaravelData\Tests\Fakes\SimpleResource;
use Spatie\LaravelData\Tests\Fakes\Transformers\ConfidentialDataCollectionTransformer;
use Spatie\LaravelData\Tests\Fakes\Transformers\ConfidentialDataTransformer;
use Spatie\LaravelData\Tests\Fakes\Transformers\StringToUpperTransformer;
use Spatie\LaravelData\Tests\Fakes\UlarData;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\LaravelData\Transformers\Transformer;
use Spatie\LaravelData\WithData;

use function Spatie\Snapshots\assertMatchesSnapshot;

it('also works by using traits and interfaces, skipping the base data class', function () {
    $data = new class ('') implements DataObject {
        use ResponsableData;
        use IncludeableData;
        use AppendableData;
        use ValidateableData;
        use WrappableData;
        use TransformableData;
        use BaseData;
        use EmptyData;
        use ContextableData;

        public function __construct(public string $string)
        {
        }

        public static function fromString(string $string): static
        {
            return new self($string);
        }
    };

    expect($data::from('Hi')->toArray())->toMatchArray(['string' => 'Hi'])
        ->and($data::from(['string' => 'Hi']))->toEqual(new $data('Hi'))
        ->and($data::from('Hi'))->toEqual(new $data('Hi'));
});


it('can serialize and unserialize a data object', function () {
    $object = SimpleData::from('Hello world');

    $serialized = serialize($object);

    assertMatchesSnapshot($serialized);

    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(SimpleData::class);
    expect($unserialized->string)->toEqual('Hello world');
});

it('can serialize and unserialize a data object with additional data', function () {
    $object = SimpleData::from('Hello world')->additional([
        'int' => 69,
    ]);

    $serialized = serialize($object);

    assertMatchesSnapshot($serialized);

    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(SimpleData::class);
    expect($unserialized->string)->toEqual('Hello world');
    expect($unserialized->getAdditionalData())->toEqual(['int' => 69]);
});

it('during the serialization process some properties are thrown away', function () {
    $object = SimpleData::from('Hello world');

    $object->include('test');
    $object->exclude('test');
    $object->only('test');
    $object->except('test');
    $object->wrap('test');

    $unserialized = unserialize(serialize($object));

    $invaded = invade($unserialized);

    expect($invaded->_dataContext)->toBeNull();
});

it('can use data as an DTO', function () {
    $dto = SimpleDto::from('Hello World');

    expect($dto)->toBeInstanceOf(SimpleDto::class)
        ->toBeInstanceOf(Dto::class)
        ->not()->toBeInstanceOf(Data::class)
        ->not()->toHaveMethods(['toArray', 'toJson', 'toResponse', 'all', 'include', 'exclude', 'only', 'except', 'transform', 'with', 'jsonSerialize'])
        ->and($dto->string)->toEqual('Hello World');

    expect(fn () => SimpleDto::validate(['string' => null]))->toThrow(ValidationException::class);
});

it('can use data as an Resource', function () {
    $resource = SimpleResource::from('Hello World');

    expect($resource)->toBeInstanceOf(SimpleResource::class)
        ->toBeInstanceOf(Resource::class)
        ->not()->toBeInstanceOf(Data::class)
        ->toHaveMethods(['toArray', 'toJson', 'toResponse', 'all', 'include', 'exclude', 'only', 'except', 'transform', 'with', 'jsonSerialize'])
        ->and($resource->string)->toEqual('Hello World');

    expect($resource)->not()->toHaveMethods([
        'validate',
    ]);
});
