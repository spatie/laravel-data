<?php

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Support\Validation\RuleDenormalizer;
use Spatie\LaravelData\Support\Validation\RuleNormalizer;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Support\Validation\ValidationRule;
use Spatie\LaravelData\Tests\Fakes\Rules\CustomInvokableLaravelRule;
use Spatie\LaravelData\Tests\Fakes\Rules\CustomLaravelRule;
use Spatie\LaravelData\Tests\Fakes\Rules\CustomLaravelValidationRule;
use Spatie\TestTime\TestTime;

beforeEach(function () {
    TestTime::freeze(Carbon::create(2020, 05, 16, 0, 0, 0));
});

it('gets the correct rules', function (
    ValidationRule $attribute,
    string|object $expected,
    ValidationRule|null $expectedCreatedAttribute,
    ?string $exception = null,
) {
    if ($exception) {
        $this->expectException($exception);
    }

    $resolved = app(RuleDenormalizer::class)->execute($attribute, ValidationPath::create());

    expect($resolved[0])->toEqual($expected);
})->with('attributes');

it('creates the correct attributes', function (
    ValidationRule $attribute,
    string|object $expected,
    ValidationRule|null $expectedCreatedAttribute,
    ?string $exception = null,
) {
    if ($exception) {
        expect(true)->toBeTrue();

        return;
    }

    $resolved = app(RuleNormalizer::class)->execute($expected);

    expect($resolved[0])->toEqual($expectedCreatedAttribute);
})->with('attributes');

it('can use the Rule rule', function () {
    $rule = new Rule(
        'test',
        ['a', 'b', 'c'],
        'x|y',
        new CustomLaravelRule(),
        new Required()
    );

    expect(app(RuleDenormalizer::class)->execute($rule, ValidationPath::create()))->toMatchArray([
        'test',
        'a',
        'b',
        'c',
        'x',
        'y',
        new CustomLaravelRule(),
        'required',
    ]);
});

it('can use the Rule rule with invokable rules', function () {
    $rule = new Rule(
        'test',
        ['a', 'b', 'c'],
        'x|y',
        new CustomInvokableLaravelRule(),
        new Required()
    );

    expect(app(RuleDenormalizer::class)->execute($rule, ValidationPath::create()))->toMatchArray([
        'test',
        'a',
        'b',
        'c',
        'x',
        'y',
        new CustomInvokableLaravelRule(),
        'required',
    ]);
});

it('can use the Rule rule with validation rule contract', function () {
    $rule = new Rule(
        'test',
        ['a', 'b', 'c'],
        'x|y',
        new CustomLaravelValidationRule(),
        new Required()
    );

    expect(app(RuleDenormalizer::class)->execute($rule, ValidationPath::create()))->toMatchArray([
        'test',
        'a',
        'b',
        'c',
        'x',
        'y',
        new CustomLaravelValidationRule(),
        'required',
    ]);
})->skip(
    fn() => version_compare(app()->version(), '10.0.0', '<')
);
