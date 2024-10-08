<?php

use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\DataWithMergedRuleset;
use Spatie\LaravelData\Tests\Fakes\DataWithMergedStringRuleset;

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

it('it will merge validation rules using string rules', function () {
    try {
        Data::validate(['first_name' => str_repeat('a', 1)]);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'first_name' => ['The first name field must be at least 2 characters.']
        ]);
    }

    try {
        DataWithMergedStringRuleset::validate(['first_name' => str_repeat('a', 11)]);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'first_name' => ['The first name field must not be greater than 10 characters.']
        ]);
    }

    try {
        DataWithMergedStringRuleset::validate(['first_name' => 'a123']);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'first_name' => ['The first name field must only contain letters.']
        ]);
    }
});
