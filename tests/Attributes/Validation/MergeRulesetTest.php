<?php

use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\MergeRules;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Data;

it('it will merge validation rules', function () {
    try {
        DataWithMergedRuleset::validate(['first_name' => str_repeat('a', 1)]);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'first_name' => ['The first name field must be at least 2 characters.'],
        ]);
    }

    try {
        DataWithMergedRuleset::validate(['first_name' => str_repeat('a', 11)]);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'first_name' => ['The first name field must not be greater than 10 characters.'],
        ]);
    }
});

it('it will merge validation rules using string rules', function () {
    try {
        Data::validate(['first_name' => str_repeat('a', 1)]);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'first_name' => ['The first name field must be at least 2 characters.'],
        ]);
    }

    try {
        DataWithMergedStringRuleset::validate(['first_name' => str_repeat('a', 11)]);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'first_name' => ['The first name field must not be greater than 10 characters.'],
        ]);
    }

    try {
        DataWithMergedStringRuleset::validate(['first_name' => 'a123']);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'first_name' => ['The first name field must only contain letters.'],
        ]);
    }
});

it('it will skip validation rules when skipping validation', function () {
    $data = DataWithSkippedValidationMergedRuleset::validateAndCreate(['first_name' => str_repeat('1', 20)]);
    expect($data->first_name)->toBe(str_repeat('1', 20));
});

#[MergeRules]
class DataWithMergedRuleset extends Data
{
    public function __construct(
        #[Max(10)]
        public string $first_name,
    ) {
    }

    public static function rules(): array
    {
        return [
            'first_name' => ['min:2'],
        ];
    }
}

#[MergeRules]
class DataWithMergedStringRuleset extends Data
{
    public function __construct(
        #[Max(10)]
        public string $first_name,
    ) {
    }

    public static function rules(): array
    {
        return [
            'first_name' => 'min:2|alpha',
        ];
    }
}

#[MergeRules]
class DataWithSkippedValidationMergedRuleset extends Data
{
    public function __construct(
        #[Max(10), WithoutValidation]
        public string $first_name,
    ) {
    }

    public static function rules(): array
    {
        return [
            'first_name' => 'min:2|alpha',
        ];
    }
}
