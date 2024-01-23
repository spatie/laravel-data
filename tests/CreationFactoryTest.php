<?php

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Casting\GlobalCastsCollection;
use Spatie\LaravelData\Tests\Fakes\Casts\MeaningOfLifeCast;
use Spatie\LaravelData\Tests\Fakes\Casts\StringToUpperCast;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('can disable the use of magical methods', function () {
    $data = new class ('', '') extends Data {
        public function __construct(
            public ?string $id,
            public string $name,
        ) {
        }

        public static function fromArray(array $payload)
        {
            return new self(
                id: $payload['hash_id'] ?? null,
                name: $payload['name'],
            );
        }
    };

    expect(
        $data::factory()->withoutMagicalCreation()->from(['hash_id' => 1, 'name' => 'Taylor'])
    )->toEqual(new $data(null, 'Taylor'));

    expect($data::from(['hash_id' => 1, 'name' => 'Taylor']))
        ->toEqual(new $data(1, 'Taylor'));
});

it('can create data ignoring certain magical methods', function () {
    $data = new class ('', '') extends Data {
        public function __construct(
            public ?string $id,
            public string $name,
        ) {
        }

        public static function fromArray(array $payload)
        {
            return new self(
                id: $payload['hash_id'] ?? null,
                name: $payload['name'],
            );
        }
    };

    expect(
        $data::factory()->ignoreMagicalMethod('fromArray')->from(['hash_id' => 1, 'name' => 'Taylor'])
    )->toEqual(new $data(null, 'Taylor'));

    expect(
        $data::factory()->from(['hash_id' => 1, 'name' => 'Taylor']),
    )->toEqual(new $data(1, 'Taylor'));
});

it('can enable the validation of non request payloads', function () {
    $dataClass = new class () extends Data {
        #[In('Hello World')]
        public string $string;
    };

    $payload = [
        'string' => 'nowp',
    ];

    expect($dataClass::factory()->from($payload))
        ->toBeInstanceOf(Data::class)
        ->string->toEqual('nowp');

    expect(fn () => $dataClass::factory()->alwaysValidate()->from($payload))
        ->toThrow(ValidationException::class);
});

it('can disable the validation request payloads', function () {
    $dataClass = new class () extends Data {
        #[In('Hello World')]
        public string $string;
    };

    $request = request()->merge([
        'string' => 'nowp',
    ]);

    expect(fn () => $dataClass::factory()->from($request))
        ->toThrow(ValidationException::class);

    expect($dataClass::factory()->withoutValidation()->from($request))
        ->toBeInstanceOf(Data::class)
        ->string->toEqual('nowp');
});

it('can disable property mapping', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('firstName')]
        public string $first_name;
    };

    expect($dataClass::factory()->withoutPropertyNameMapping()->from(['firstName' => 'Taylor']))
        ->toBeInstanceOf(Data::class)
        ->first_name->toBeEmpty();
});

it('can add a new global cast', function () {
    $data = SimpleData::factory()->withCast('string', new StringToUpperCast())->from([
        'string' => 'Hello World',
    ]);

    expect($data)->string->toEqual('HELLO WORLD');
});

it('can add a collection of global casts', function () {
    $castCollection = new GlobalCastsCollection([
        'string' => new StringToUpperCast(),
        'int' => new MeaningOfLifeCast(),
    ]);

    $dataClass = new class () extends Data {
        public string $string;

        public int $int;
    };

    $data = $dataClass::factory()->withCastCollection($castCollection)->from([
        'string' => 'Hello World',
        'int' => '123',
    ]);

    expect($data)->string->toEqual('HELLO WORLD');
    expect($data)->int->toEqual(42);
});

it('can collect using a factory', function () {
    $collection = SimpleData::factory()->withCast('string', new StringToUpperCast())->collect([
        ['string' => 'Hello World'],
        ['string' => 'Hello You'],
    ], Collection::class);

    expect($collection)
        ->toHaveCount(2)
        ->first()->string->toEqual('HELLO WORLD')
        ->last()->string->toEqual('HELLO YOU');
});
