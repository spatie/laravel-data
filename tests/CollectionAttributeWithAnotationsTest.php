<?php

use Spatie\LaravelData\Tests\Fakes\Collections\SimpleDataCollectionWithAnotations;
use Spatie\LaravelData\Tests\Fakes\DataWithSimpleDataCollectionWithAnotations;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestSupport\DataValidationAsserter;

beforeEach(function () {
    $this->payload = [
        'collection' => [
            ['string' => 'string1'],
            ['string' => 'string2'],
            ['string' => 'string3'],
        ],
    ];
});

it('can create a data object with a collection attribute from array and back', function () {

    $data = DataWithSimpleDataCollectionWithAnotations::from($this->payload);

    expect($data)->toEqual(new DataWithSimpleDataCollectionWithAnotations(
        collection: new SimpleDataCollectionWithAnotations([
            new SimpleData(string: 'string1'),
            new SimpleData(string: 'string2'),
            new SimpleData(string: 'string3'),
        ])
    ));

    expect($data->toArray())->toBe($this->payload);
});

it('can validate a data object with a collection attribute', function () {

    DataValidationAsserter::for(DataWithSimpleDataCollectionWithAnotations::class)
        ->assertOk($this->payload)
        ->assertErrors(['collection' => [
            ['notExistingAttribute' => 'xxx'],
        ]])
        ->assertRules(
            rules: [
                'collection' => ['present', 'array'],
                'collection.0.string' => ['required', 'string'],
                'collection.1.string' => ['required', 'string'],
                'collection.2.string' => ['required', 'string'],
            ],
            payload: $this->payload
        );
});
