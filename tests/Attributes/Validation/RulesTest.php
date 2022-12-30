<?php

use Carbon\Carbon;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Support\Validation\RulesMapper;
use Spatie\LaravelData\Support\Validation\ValidationRule;
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

    expect($attribute->getRules())->toMatchArray([$expected]);
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

    $resolved = app(RulesMapper::class)->execute([$expected]);

    expect($resolved[0])->toEqual($expectedCreatedAttribute);
})->with('attributes');

it('can use the Rule rule', function () {
    $laravelRule = new class () implements RuleContract {
        public function passes($attribute, $value)
        {
        }

        public function message()
        {
        }
    };

    $rule = new Rule(
        'test',
        ['a', 'b', 'c'],
        'x|y',
        'regex:/test|ok/',
        $laravelRule,
        new Required()
    );

    expect($rule->getRules())->toMatchArray([
        'test',
        'a',
        'b',
        'c',
        'x',
        'y',
        'regex:/test|ok/',
        $laravelRule,
        'required',
    ]);
});

it('can use the Rule rule with invokable rules', function () {
    onlyPHP81();

    if (version_compare($this->app->version(), '9.18', '<')) {
        $this->markTestIncomplete('Invokable rules are only available in Laravel 9.18.');
    }

    $invokableLaravelRule = new class () implements InvokableRule {
        public function __invoke($attribute, $value, $fail)
        {
        }
    };

    $rule = new Rule(
        'test',
        ['a', 'b', 'c'],
        'x|y',
        'regex:/test|ok/',
        $invokableLaravelRule,
        new Required()
    );

    expect($rule->getRules())->toMatchArray([
        'test',
        'a',
        'b',
        'c',
        'x',
        'y',
        'regex:/test|ok/',
        $invokableLaravelRule,
        'required',
    ]);
});
