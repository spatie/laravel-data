<?php

use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Tests\Fakes\MultiData;

it('can create a data object from JSON', function () {
    $originalData = new MultiData('Hello', 'World');

    $createdData = MultiData::from($originalData->toJson());

    expect($createdData)->toEqual($originalData);
});

it("won't create a data object from a regular string", function () {
    MultiData::from('Hello World');
})->throws(CannotCreateData::class);
