<?php

use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Tests\Fakes\DataWithMergedRuleset;

it('it will merge validation rules', function () {
    try {
        DataWithMergedRuleset::validate(['first_name' => str_repeat('a', 1)]);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'first_name' => ['The first name field must be at least 2 characters.']
        ]);
    }

    try {
        DataWithMergedRuleset::validate(['first_name' => str_repeat('a', 11)]);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'first_name' => ['The first name field must not be greater than 10 characters.']
        ]);
    }
});

