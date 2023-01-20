<?php

use Illuminate\Contracts\Validation\Rule as CustomRuleContract;
use Illuminate\Validation\Rules\Exists as BaseExists;
use Spatie\LaravelData\Attributes\Validation\Dimensions;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Support\Validation\RulesMapper;

beforeEach(function () {
    $this->mapper = resolve(RulesMapper::class);
});

it('can map string rules')
    ->expect(fn () => $this->mapper->execute(['required'], null))
    ->toEqual([new Required()]);

it('can map string rules with arguments')
    ->expect(fn () => $this->mapper->execute(['exists:users'], null))
    ->toEqual([new Exists(rule: new BaseExists('users'))]);

it('can map string rules with key-value arguments')
    ->expect(fn () => $this->mapper->execute(['dimensions:min_width=100,min_height=200'], null))
    ->toEqual([new Dimensions(minWidth: 100, minHeight: 200)]);

it('can map multiple rules')
    ->expect(fn () => $this->mapper->execute(['required', 'min:0'], null))
    ->toEqual([new Required(), new Min(0)]);

it('can map multiple concatenated rules')
    ->expect(fn () => $this->mapper->execute(['required|min:0'], null))
    ->toEqual([new Required(), new Min(0)]);

it('can map faulty rules')
    ->expect(fn () => $this->mapper->execute(['min:'], null))
    ->toEqual([new Rule('min:')]);

it('can map Laravel rule objects')
    ->expect(fn () => $this->mapper->execute([new BaseExists('users')], null))
    ->toEqual([new Exists('users')]);

it('can map a custom Laravel rule objects', function () {
    $rule = new class () implements CustomRuleContract {
        public function passes($attribute, $value)
        {
        }

        public function message()
        {
        }
    };

    expect($this->mapper->execute([$rule], null))->toEqual([new Rule($rule)]);
});
