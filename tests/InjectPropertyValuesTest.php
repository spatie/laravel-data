<?php

namespace Spatie\LaravelData\Tests;

use Attribute;
use Illuminate\Http\Request;

use function Pest\Laravel\mock;

use Spatie\LaravelData\Attributes\Concerns\ResolvesPropertyForInjectedValue;
use Spatie\LaravelData\Attributes\InjectsPropertyValue;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Skipped;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TestFromInjectingParameter implements InjectsPropertyValue
{
    public static mixed $payload;

    public function __construct(
        public string $parameter,
        public bool $replaceWhenPresentInBody = true
    ) {
    }

    public function resolve(DataProperty $dataProperty, mixed $payload, array $properties, CreationContext $creationContext): mixed
    {
        return static::$payload[$this->parameter] ?? Skipped::create();
    }

    public function shouldBeReplacedWhenPresentInPayload(): bool
    {
        return $this->replaceWhenPresentInBody;
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class TestFromInjectingParameterProperty extends TestFromInjectingParameter
{
    use ResolvesPropertyForInjectedValue;

    public function __construct(
        string $parameter,
        public ?string $key = null,
        bool $replaceWhenPresentInBody = true
    ) {
        parent::__construct($parameter, $replaceWhenPresentInBody);
    }

    public function resolve(DataProperty $dataProperty, mixed $payload, array $properties, CreationContext $creationContext): mixed
    {
        return $this->resolvePropertyForInjectedValue(
            $dataProperty,
            $payload,
            $properties,
            $creationContext
        );
    }

    protected function getPropertyKey(): string|null
    {
        return $this->key;
    }
}

it('can fill data properties with injected parameters', function () {
    TestFromInjectingParameter::$payload = [
        'string' => 'Hello World',
        'array' => ['a', 'b'],
        'data' => new SimpleData('Hello World'),
    ];

    $dataClass = new class () extends Data {
        #[TestFromInjectingParameter('string')]
        public string $string;

        #[TestFromInjectingParameter('array')]
        public array $array;

        #[TestFromInjectingParameter('data')]
        public SimpleData $data;
    };

    $data = $dataClass::from();

    expect($data->string)->toEqual('Hello World');
    expect($data->array)->toEqual(['a', 'b']);
    expect($data->data)->toBeInstanceOf(SimpleData::class);
    expect($data->data->string)->toEqual('Hello World');
});

it('can fill data properties from injected parameter properties', function () {
    TestFromInjectingParameterProperty::$payload = [
        'object' => (object) ['title' => 'Rick'],
        'array' => ['description' => 'Astley'],
    ];

    $dataClass = new class () extends Data {
        #[TestFromInjectingParameterProperty('object')]
        public string $title;

        #[TestFromInjectingParameterProperty('array')]
        public string $description;
    };

    $data = $dataClass::from();

    expect($data->title)->toEqual('Rick');
    expect($data->description)->toEqual('Astley');
});

it('can fill data properties from injected parameters using custom property mapping ', function () {
    TestFromInjectingParameterProperty::$payload = TestFromInjectingParameter::$payload = [
        'something' => [
            'name' => 'Something',
            'nested' => [
                'foo' => 'bar',
            ],
            'tags' => ['foo', 'bar'],
            'rows' => [
                ['total' => 10],
                ['total' => 20],
                ['total' => 30],
            ],
        ],
        'user_id' => 1,
    ];

    $dataClass = new class () extends Data {
        #[TestFromInjectingParameterProperty('something', 'name')]
        public string $title;

        #[TestFromInjectingParameterProperty('something', 'nested.foo')]
        public string $foo;

        #[TestFromInjectingParameterProperty('something', 'tags.0')]
        public string $tag;

        #[TestFromInjectingParameterProperty('something', 'rows.*.total')]
        public array $totals;

        #[TestFromInjectingParameter('user_id')]
        #[MapInputName('user_id')]
        public int $userId;
    };

    $data = $dataClass::from();

    expect($data->title)->toEqual('Something');
    expect($data->foo)->toEqual('bar');
    expect($data->tag)->toEqual('foo');
    expect($data->totals)->toEqual([10, 20, 30]);
    expect($data->userId)->toEqual(1);
});

it('replaces properties when injected parameter properties exist', function () {
    TestFromInjectingParameterProperty::$payload = TestFromInjectingParameterProperty::$payload = [
        'foo' => 'Rick',
        'bar' => ['slug' => 'Astley'],
        'user_id' => 2,
    ];

    $dataClass = new class () extends Data {
        #[TestFromInjectingParameter('foo')]
        public string $name;

        #[TestFromInjectingParameterProperty('bar')]
        public string $slug;

        #[TestFromInjectingParameter('user_id')]
        #[MapInputName('user_id')]
        public int $userId;
    };

    $data = $dataClass::from([
        'name' => 'Jon',
        'slug' => 'Bon Jovi',
    ]);

    expect($data->name)->toEqual('Rick');
    expect($data->slug)->toEqual('Astley');
    expect($data->userId)->toEqual(2);
});

it('skips replacing properties when route parameter properties exist and replacing is disabled', function () {
    TestFromInjectingParameter::$payload = TestFromInjectingParameterProperty::$payload = [];

    $dataClass = new class () extends Data {
        #[TestFromInjectingParameter('foo', replaceWhenPresentInBody: false)]
        public string $name;

        #[TestFromInjectingParameterProperty('bar', replaceWhenPresentInBody: false)]
        public string $slug;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('foo')->never();
    $requestMock->expects('route')->with('bar')->never();
    $requestMock->expects('toArray')->andReturns([
        'name' => 'Jon',
        'slug' => 'Bon Jovi',
    ]);

    $data = $dataClass::from($requestMock);

    expect($data->name)->toEqual('Jon');
    expect($data->slug)->toEqual('Bon Jovi');
});

it('skips properties it cannot find a route parameter for', function () {
    TestFromInjectingParameter::$payload = TestFromInjectingParameterProperty::$payload = [];

    $dataClass = new class () extends Data {
        #[TestFromInjectingParameter('foo')]
        public string $name;

        #[TestFromInjectingParameterProperty('bar')]
        public ?string $slug = 'default-slug';
    };

    $data = $dataClass::from([
        'name' => 'Jon Bon Jovi',
    ]);

    expect($data->name)->toEqual('Jon Bon Jovi');
    expect($data->slug)->toEqual('default-slug');
});
