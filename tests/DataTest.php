<?php

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\BaseData;
use Spatie\LaravelData\Concerns\ContextableData;
use Spatie\LaravelData\Concerns\EmptyData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\ValidateableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\AppendableData as AppendableDataContract;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;
use Spatie\LaravelData\Contracts\EmptyData as EmptyDataContract;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Contracts\ResponsableData as ResponsableDataContract;
use Spatie\LaravelData\Contracts\TransformableData as TransformableDataContract;
use Spatie\LaravelData\Contracts\ValidateableData as ValidateableDataContract;
use Spatie\LaravelData\Contracts\WrappableData as WrappableDataContract;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Dto;
use Spatie\LaravelData\Resource;
use Spatie\LaravelData\Tests\Fakes\SimpleDto;
use Spatie\LaravelData\Tests\Fakes\SimpleResource;

it('also works by using traits and interfaces, skipping the base data class', function () {
    $data = new class ('') implements Responsable, AppendableDataContract, BaseDataContract, TransformableDataContract, IncludeableDataContract, ResponsableDataContract, ValidateableDataContract, WrappableDataContract, EmptyDataContract {
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


it('can use data as an DTO', function () {
    $dto = SimpleDto::from('Hello World');

    expect($dto)->toBeInstanceOf(SimpleDto::class)
        ->toBeInstanceOf(Dto::class)
        ->not()->toBeInstanceOf(Data::class)
        ->not()->toHaveMethods(['toArray', 'toJson', 'toResponse', 'all', 'include', 'exclude', 'only', 'except', 'transform', 'with', 'jsonSerialize'])
        ->and($dto->string)->toEqual('Hello World');

    expect(fn () => SimpleDto::validate(['string' => null]))->toThrow(ValidationException::class);
})->skip('Fix test for Laravel 12');

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
})->skip('Fix test for Laravel 12');
