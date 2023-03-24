<?php

use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Normalizers\FormRequestNormalizer;
use Spatie\LaravelData\Tests\Fakes\DataWithNullable;

beforeEach(function () {
    config()->set('data.normalizers', [FormRequestNormalizer::class]);
});

it('can create a data object from FormRequest', function () {
    $request = new class () extends FormRequest {
        public function rules(): array
        {
            return [
                'string' => 'required|string',
                'nullableString' => 'nullable|string',
            ];
        }
    };
    $request
        ->replace([
            'string' => 'Hello',
            'nullableString' => 'World',
        ])
        ->setContainer(app())
        ->validateResolved();

    $originalData = new DataWithNullable('Hello', 'World');
    $createdData = DataWithNullable::from($request);

    expect($createdData)->toEqual($originalData);
});

it("excludes unsafe data", function () {
    $request = new class () extends FormRequest {
        public function rules(): array
        {
            return [
                'string' => 'required|string',
            ];
        }
    };
    $request
        ->replace([
            'string' => 'Hello',
            'nullableString' => 'World',
        ])
        ->setContainer(app())
        ->validateResolved();

    $originalData = new DataWithNullable('Hello', null);
    $createdData = DataWithNullable::from($request);

    expect($createdData)->toEqual($originalData);
});
