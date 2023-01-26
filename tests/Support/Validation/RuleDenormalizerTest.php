<?php

use Carbon\CarbonImmutable;
use Carbon\CarbonTimeZone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule as LaravelRule;

use function Pest\Laravel\mock;

use Spatie\LaravelData\Attributes\Validation\AcceptedIf;
use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Attributes\Validation\EndsWith;
use Spatie\LaravelData\Attributes\Validation\ExcludeWithout;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule as RuleAttribute;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\RuleDenormalizer;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Rules\CustomInvokableLaravelRule;
use Spatie\LaravelData\Tests\Fakes\Rules\CustomLaravelRule;

it('can denormalize rules', function ($rule, $expected, $path = null) {
    $denormalizer = new RuleDenormalizer();

    expect($denormalizer->execute($rule, $path ?? new ValidationPath(null)))->toEqual($expected);
})->with([
    'string rule' => ['string', ['string']],
    'multi rule string' => ['string|required', ['string', 'required']],
    'array rule' => [['string|min:3', 'required'], ['string', 'min:3', 'required']],
    'string validation attribute rule' => [new Required(), ['required']],
    'string validation attribute rule with parameters' => [new Min(3), ['min:3']],
    'string validation attribute rule with parameters to normalize' => [new AcceptedIf('field', DummyBackedEnum::BOO), ['accepted_if:field,boo']],
    'object validation attribute rule' => [new In('a', 'b'), [LaravelRule::in('a', 'b')]],
    'rule attribute rule' => [new RuleAttribute('string|required', new Min(3)), ['string', 'required', 'min:3']],
    'laravel custom rule' => [new CustomLaravelRule(), [new CustomLaravelRule()]],
    'laravel custom invokable rule' => [new CustomInvokableLaravelRule(), [new CustomInvokableLaravelRule()]],

    // Parameters
    'boolean true parameter' => [new AcceptedIf('field', true), ['accepted_if:field,true']],
    'boolean false parameter' => [new AcceptedIf('field', false), ['accepted_if:field,false']],
    'enum parameter' => [new AcceptedIf('field', DummyBackedEnum::BOO), ['accepted_if:field,boo']],
    'empty array parameter' => [new EndsWith(), ['ends_with']],
    'array parameter' => [new EndsWith(['test', DummyBackedEnum::BOO]), ['ends_with:test,boo']],
    'date parameter' => [new After(CarbonImmutable::create(2020, 05, 16, 12, tz: new CarbonTimeZone('Europe/Brussels'))), ['after:2020-05-16T12:00:00+02:00']],
    'field root reference parameter' => [new ExcludeWithout(new FieldReference('field')), ['exclude_without:field']],
    'field nested reference parameter' => [new ExcludeWithout(new FieldReference('field')), ['exclude_without:nested.field'], new ValidationPath('nested')],
]);

it('can denormalize rules with route parameter references', function () {
    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('parameter')->andReturns('69');
    $this->app->bind('request', fn () => $requestMock);

    $denormalizer = new RuleDenormalizer();

    expect($denormalizer->execute(new Min(new RouteParameterReference('parameter')), new ValidationPath(null)))->toEqual([
        'min:69',
    ]);
});
