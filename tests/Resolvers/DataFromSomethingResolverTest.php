<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;
use Spatie\LaravelData\Tests\Fakes\DataWithMultipleArgumentCreationMethod;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModel;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts;

it('can create data from a custom method', function () {
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

it('can create data from a custom method with an interface parameter', function () {
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

it('can create data from a custom method with an inherit parameter', function () {
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

it('can create data from a custom method with multiple parameters', function () {
    expect(DataWithMultipleArgumentCreationMethod::from('Rick Astley', 42))
        ->toEqual(new DataWithMultipleArgumentCreationMethod('Rick Astley_42'));
});

it('can create data without custom creation methods', function () {
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
        $data::withoutMagicalCreationFrom(['hash_id' => 1, 'name' => 'Taylor'])
    )->toEqual(new $data(null, 'Taylor'));

    expect($data::from(['hash_id' => 1, 'name' => 'Taylor']))
        ->toEqual(new $data(1, 'Taylor'));
});

it('can create data ignoring certain magical methods', function () {
    class DummyA extends Data
    {
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
    }

    expect(
        app(DataFromSomethingResolver::class)->reset()->ignoreMagicalMethods('fromArray')->execute(DummyA::class, ['hash_id' => 1, 'name' => 'Taylor'])
    )->toEqual(new DummyA(null, 'Taylor'));

    expect(
        app(DataFromSomethingResolver::class)->reset()->execute(DummyA::class, ['hash_id' => 1, 'name' => 'Taylor'])
    )->toEqual(new DummyA(1, 'Taylor'));
});
