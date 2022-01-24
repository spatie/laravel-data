<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Carbon\Carbon;
use Generator;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rules\Enum as EnumRule;
use Illuminate\Validation\Rules\Exists as BaseExists;
use Illuminate\Validation\Rules\In as BaseIn;
use Illuminate\Validation\Rules\NotIn as BaseNotIn;
use Illuminate\Validation\Rules\Password as BasePassword;
use Illuminate\Validation\Rules\Unique as BaseUnique;
use Spatie\LaravelData\Attributes\Validation\Accepted;
use Spatie\LaravelData\Attributes\Validation\AcceptedIf;
use Spatie\LaravelData\Attributes\Validation\ActiveUrl;
use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\Alpha;
use Spatie\LaravelData\Attributes\Validation\AlphaDash;
use Spatie\LaravelData\Attributes\Validation\AlphaNumeric;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Bail;
use Spatie\LaravelData\Attributes\Validation\Before;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\CurrentPassword;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\DateEquals;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Different;
use Spatie\LaravelData\Attributes\Validation\Digits;
use Spatie\LaravelData\Attributes\Validation\DigitsBetween;
use Spatie\LaravelData\Attributes\Validation\Dimensions;
use Spatie\LaravelData\Attributes\Validation\Distinct;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\EndsWith;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\ExcludeIf;
use Spatie\LaravelData\Attributes\Validation\ExcludeUnless;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\File;
use Spatie\LaravelData\Attributes\Validation\Filled;
use Spatie\LaravelData\Attributes\Validation\GreaterThan;
use Spatie\LaravelData\Attributes\Validation\GreaterThanOrEqualTo;
use Spatie\LaravelData\Attributes\Validation\Image;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\InArray;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\IP;
use Spatie\LaravelData\Attributes\Validation\IPv4;
use Spatie\LaravelData\Attributes\Validation\IPv6;
use Spatie\LaravelData\Attributes\Validation\Json;
use Spatie\LaravelData\Attributes\Validation\LessThan;
use Spatie\LaravelData\Attributes\Validation\LessThanOrEqualTo;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Attributes\Validation\MimeTypes;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\MultipleOf;
use Spatie\LaravelData\Attributes\Validation\NotIn;
use Spatie\LaravelData\Attributes\Validation\NotRegex;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Prohibited;
use Spatie\LaravelData\Attributes\Validation\ProhibitedIf;
use Spatie\LaravelData\Attributes\Validation\ProhibitedUnless;
use Spatie\LaravelData\Attributes\Validation\Prohibits;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\RequiredIf;
use Spatie\LaravelData\Attributes\Validation\RequiredUnless;
use Spatie\LaravelData\Attributes\Validation\RequiredWith;
use Spatie\LaravelData\Attributes\Validation\RequiredWithAll;
use Spatie\LaravelData\Attributes\Validation\RequiredWithout;
use Spatie\LaravelData\Attributes\Validation\RequiredWithoutAll;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Same;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\Validation\StartsWith;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Timezone;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Attributes\Validation\ValidationAttribute;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\TestTime\TestTime;

class RulesTest extends TestCase
{
    /**
     * @test
     * @dataProvider attributesDataProvider
     */
    public function it_gets_the_correct_rules(
        ValidationAttribute $attribute,
        array $expected = [],
        ?string $exception = null
    ) {
        if ($exception) {
            $this->expectException($exception);
        }

        $this->assertEquals($expected, $attribute->getRules());
    }

    public function attributesDataProvider(): Generator
    {
        TestTime::freeze(Carbon::create(2020, 05, 16, 0, 0, 0));

        yield from $this->acceptedIfAttributesDataProvider();
        yield from $this->afterAttributesDataProvider();
        yield from $this->afterOrEqualAttributesDataProvider();
        yield from $this->arrayTypeAttributesDataProvider();
        yield from $this->beforeAttributesDataProvider();
        yield from $this->beforeOrEqualAttributesDataProvider();
        yield from $this->betweenAttributesDataProvider();
        yield from $this->currentPasswordAttributesDataProvider();
        yield from $this->dateEqualsAttributesDataProvider();
        yield from $this->dimensionsAttributesDataProvider();
        yield from $this->distinctAttributesDataProvider();
        yield from $this->emailAttributesDataProvider();
        yield from $this->endsWithAttributesDataProvider();
        yield from $this->existsAttributesDataProvider();
        yield from $this->inAttributesDataProvider();
        yield from $this->mimesAttributesDataProvider();
        yield from $this->mimeTypesAttributesDataProvider();
        yield from $this->notInAttributesDataProvider();
        yield from $this->passwordAttributesDataProvider();
        yield from $this->prohibitedIfAttributesDataProvider();
        yield from $this->prohibitedUnlessAttributesDataProvider();
        yield from $this->prohibitsAttributesDataProvider();
        yield from $this->requiredIfAttributesDataProvider();
        yield from $this->requiredUnlessAttributesDataProvider();
        yield from $this->requiredWithAttributesDataProvider();
        yield from $this->requiredWithAllAttributesDataProvider();
        yield from $this->requiredWithoutAttributesDataProvider();
        yield from $this->requiredWithoutAllAttributesDataProvider();
        yield from $this->ruleAttributesDataProvider();
        yield from $this->startsWithAttributesDataProvider();
        yield from $this->uniqueAttributesDataProvider();

        yield [
            'attribute' => new Accepted(),
            'expected' => ['accepted'],
        ];

        yield [
            'attribute' => new ActiveUrl(),
            'expected' => ['active_url'],
        ];

        yield [
            'attribute' => new Alpha(),
            'expected' => ['alpha'],
        ];

        yield [
            'attribute' => new AlphaDash(),
            'expected' => ['alpha_dash'],
        ];

        yield [
            'attribute' => new AlphaNumeric(),
            'expected' => ['alpha_num'],
        ];

        yield [
            'attribute' => new Bail(),
            'expected' => ['bail'],
        ];

        yield [
            'attribute' => new BooleanType(),
            'expected' => ['boolean'],
        ];

        yield [
            'attribute' => new Confirmed(),
            'expected' => ['confirmed'],
        ];

        yield [
            'attribute' => new Date(),
            'expected' => ['date'],
        ];

        yield [
            'attribute' => new DateFormat('Y-m-d'),
            'expected' => ['date_format:Y-m-d'],
        ];

        yield [
            'attribute' => new Different('field'),
            'expected' => ['different:field'],
        ];

        yield [
            'attribute' => new Digits(10),
            'expected' => ['digits:10'],
        ];

        yield [
            'attribute' => new DigitsBetween(5, 10),
            'expected' => ['digits_between:5,10'],
        ];

        yield [
            'attribute' => new Enum('enum_class'),
            'expected' => [new EnumRule('enum_class')],
        ];


        yield [
            'attribute' => new ExcludeIf('field', true),
            'expected' => ['exclude_if:field,true'],
        ];

        yield [
            'attribute' => new ExcludeUnless('field', 42),
            'expected' => ['exclude_unless:field,42'],
        ];

        yield [
            'attribute' => new File(),
            'expected' => ['file'],
        ];

        yield [
            'attribute' => new Filled(),
            'expected' => ['filled'],
        ];

        yield [
            'attribute' => new GreaterThan('field'),
            'expected' => ['gt:field'],
        ];

        yield [
            'attribute' => new GreaterThanOrEqualTo('field'),
            'expected' => ['gte:field'],
        ];

        yield [
            'attribute' => new Image(),
            'expected' => ['image'],
        ];

        yield [
            'attribute' => new InArray('field'),
            'expected' => ['in_array:field'],
        ];

        yield [
            'attribute' => new IntegerType(),
            'expected' => ['integer'],
        ];

        yield [
            'attribute' => new IP(),
            'expected' => ['ip'],
        ];

        yield [
            'attribute' => new IPv4(),
            'expected' => ['ipv4'],
        ];

        yield [
            'attribute' => new IPv6(),
            'expected' => ['ipv6'],
        ];


        yield [
            'attribute' => new Json(),
            'expected' => ['json'],
        ];

        yield [
            'attribute' => new LessThan('field'),
            'expected' => ['lt:field'],
        ];

        yield [
            'attribute' => new LessThanOrEqualTo('field'),
            'expected' => ['lte:field'],
        ];

        yield [
            'attribute' => new Max(10),
            'expected' => ['max:10'],
        ];

        yield [
            'attribute' => new Min(10),
            'expected' => ['min:10'],
        ];

        yield [
            'attribute' => new MultipleOf(10),
            'expected' => ['multiple_of:10'],
        ];

        yield [
            'attribute' => new NotRegex('/^.+$/i'),
            'expected' => ['not_regex:/^.+$/i'],
        ];

        yield [
            'attribute' => new Nullable(),
            'expected' => ['nullable'],
        ];

        yield [
            'attribute' => new Numeric(),
            'expected' => ['numeric'],
        ];

        yield [
            'attribute' => new Present(),
            'expected' => ['present'],
        ];

        yield [
            'attribute' => new Prohibited(),
            'expected' => ['prohibited'],
        ];

        yield [
            'attribute' => new Regex('/^.+$/i'),
            'expected' => ['regex:/^.+$/i'],
        ];

        yield [
            'attribute' => new Required(),
            'expected' => ['required'],
        ];

        yield [
            'attribute' => new Same('field'),
            'expected' => ['same:field'],
        ];

        yield [
            'attribute' => new Size(10),
            'expected' => ['size:10'],
        ];

        yield [
            'attribute' => new StringType(),
            'expected' => ['string'],
        ];

        yield [
            'attribute' => new Timezone(),
            'expected' => ['timezone'],
        ];

        yield [
            'attribute' => new Url(),
            'expected' => ['url'],
        ];

        yield [
            'attribute' => new Uuid(),
            'expected' => ['uuid'],
        ];
    }

    public function acceptedIfAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new AcceptedIf('value', 'string'),
            'expected' => ['accepted_if:value,string'],
        ];

        yield [
            'attribute' => new AcceptedIf('value', true),
            'expected' => ['accepted_if:value,true'],
        ];

        yield [
            'attribute' => new AcceptedIf('value', 42),
            'expected' => ['accepted_if:value,42'],
        ];

        yield [
            'attribute' => new AcceptedIf('value', 3.14),
            'expected' => ['accepted_if:value,3.14'],
        ];
    }

    public function afterAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new After('some_field'),
            'expected' => ['after:some_field'],
        ];

        yield [
            'attribute' => new After(Carbon::yesterday()),
            'expected' => ['after:2020-05-15T00:00:00+00:00'],
        ];
    }

    public function afterOrEqualAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new AfterOrEqual('some_field'),
            'expected' => ['after_or_equal:some_field'],
        ];

        yield [
            'attribute' => new AfterOrEqual(Carbon::yesterday()),
            'expected' => ['after_or_equal:2020-05-15T00:00:00+00:00'],
        ];
    }

    public function arrayTypeAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new ArrayType(),
            'expected' => ['array'],
        ];

        yield [
            'attribute' => new ArrayType(['a', 'b', 'c']),
            'expected' => ['array:a,b,c'],
        ];

        yield [
            'attribute' => new ArrayType('a', 'b', 'c'),
            'expected' => ['array:a,b,c'],
        ];

        yield [
            'attribute' => new ArrayType('a', ['b', 'c']),
            'expected' => ['array:a,b,c'],
        ];
    }

    public function beforeAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new Before('some_field'),
            'expected' => ['before:some_field'],
        ];

        yield [
            'attribute' => new Before(Carbon::yesterday()),
            'expected' => ['before:2020-05-15T00:00:00+00:00'],
        ];
    }

    public function beforeOrEqualAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new BeforeOrEqual('some_field'),
            'expected' => ['before_or_equal:some_field'],
        ];

        yield [
            'attribute' => new BeforeOrEqual(Carbon::yesterday()),
            'expected' => ['before_or_equal:2020-05-15T00:00:00+00:00'],
        ];
    }

    public function betweenAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new Between(-42, 42),
            'expected' => ['between:-42,42'],
        ];

        yield [
            'attribute' => new Between(-3.14, 3.14),
            'expected' => ['between:-3.14,3.14'],
        ];

        yield [
            'attribute' => new Between(-3.14, 42),
            'expected' => ['between:-3.14,42'],
        ];
    }

    public function currentPasswordAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new CurrentPassword(),
            'expected' => ['current_password'],
        ];

        yield [
            'attribute' => new CurrentPassword('api'),
            'expected' => ['current_password:api'],
        ];
    }

    public function dateEqualsAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new DateEquals('tomorrow'),
            'expected' => ['date_equals:tomorrow'],
        ];

        yield [
            'attribute' => new DateEquals(Carbon::yesterday()),
            'expected' => ['date_equals:2020-05-15T00:00:00+00:00'],
        ];
    }

    public function dimensionsAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new Dimensions(minWidth: 15, minHeight: 10, maxWidth: 150, maxHeight: 100, ratio: 1),
            'expected' => ['dimensions:min_width=15,min_height=10,max_width=150,max_height=100,ratio=1'],
        ];

        yield [
            'attribute' => new Dimensions(maxWidth: 150, maxHeight: 100),
            'expected' => ['dimensions:max_width=150,max_height=100'],
        ];

        yield [
            'attribute' => new Dimensions(ratio: 1.5),
            'expected' => ['dimensions:ratio=1.5'],
        ];

        yield [
            'attribute' => new Dimensions(ratio: '3/4'),
            'expected' => ['dimensions:ratio=3/4'],
        ];

        yield [
            'attribute' => new Dimensions(),
            'expected' => [],
            'exception' => CannotBuildValidationRule::class,
        ];
    }

    public function distinctAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new Distinct(),
            'expected' => ['distinct'],
        ];

        yield [
            'attribute' => new Distinct(Distinct::Strict),
            'expected' => ['distinct:strict'],
        ];

        yield [
            'attribute' => new Distinct(Distinct::IgnoreCase),
            'expected' => ['distinct:ignore_case'],
        ];

        yield [
            'attribute' => new Distinct('fake'),
            'expected' => [],
            'exception' => CannotBuildValidationRule::class,
        ];
    }

    public function emailAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new Email(),
            'expected' => ['email:rfc'],
        ];

        yield [
            'attribute' => new Email(Email::RfcValidation),
            'expected' => ['email:rfc'],
        ];

        yield [
            'attribute' => new Email([Email::RfcValidation, Email::NoRfcWarningsValidation]),
            'expected' => ['email:rfc,strict'],
        ];

        yield [
            'attribute' => new Email([Email::RfcValidation, Email::NoRfcWarningsValidation, Email::DnsCheckValidation, Email::SpoofCheckValidation, Email::FilterEmailValidation]),
            'expected' => ['email:rfc,strict,dns,spoof,filter'],
        ];

        yield [
            'attribute' => new Email(Email::RfcValidation, Email::NoRfcWarningsValidation),
            'expected' => ['email:rfc,strict'],
        ];

        yield [
            'attribute' => new Email(['fake']),
            'expected' => [],
            'exception' => CannotBuildValidationRule::class,
        ];
    }

    public function endsWithAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new EndsWith('x'),
            'expected' => ['ends_with:x'],
        ];

        yield [
            'attribute' => new EndsWith(['x', 'y']),
            'expected' => ['ends_with:x,y'],
        ];

        yield [
            'attribute' => new EndsWith('x', 'y'),
            'expected' => ['ends_with:x,y'],
        ];
    }

    public function existsAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new Exists('users'),
            'expected' => [new BaseExists('users')],
        ];

        yield [
            'attribute' => new Exists('users', 'email'),
            'expected' => [new BaseExists('users', 'email')],
        ];

        yield [
            'attribute' => new Exists('users', 'email', connection: 'tenant'),
            'expected' => [new BaseExists('tenant.users', 'email')],
        ];

        $closure = fn (Builder $builder) => $builder;

        yield [
            'attribute' => new Exists('users', 'email', where: $closure),
            'expected' => [(new BaseExists('users', 'email'))->where($closure)],
        ];
    }

    public function inAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new In('key'),
            'expected' => [new BaseIn(['key'])],
        ];

        yield [
            'attribute' => new In(['key', 'other']),
            'expected' => [new BaseIn(['key', 'other'])],
        ];

        yield [
            'attribute' => new In('key', 'other'),
            'expected' => [new BaseIn(['key', 'other'])],
        ];
    }

    public function mimesAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new MimeTypes('video/quicktime'),
            'expected' => ['mimestypes:video/quicktime'],
        ];

        yield [
            'attribute' => new MimeTypes(['video/quicktime', 'video/avi']),
            'expected' => ['mimestypes:video/quicktime,video/avi'],
        ];

        yield [
            'attribute' => new MimeTypes('video/quicktime', 'video/avi'),
            'expected' => ['mimestypes:video/quicktime,video/avi'],
        ];
    }

    public function mimeTypesAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new Mimes('jpg'),
            'expected' => ['mimes:jpg'],
        ];

        yield [
            'attribute' => new Mimes(['jpg', 'png']),
            'expected' => ['mimes:jpg,png'],
        ];

        yield [
            'attribute' => new Mimes('jpg', 'png'),
            'expected' => ['mimes:jpg,png'],
        ];
    }

    public function notInAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new NotIn('key'),
            'expected' => [new BaseNotIn(['key'])],
        ];

        yield [
            'attribute' => new NotIn(['key', 'other']),
            'expected' => [new BaseNotIn(['key', 'other'])],
        ];

        yield [
            'attribute' => new NotIn('key', 'other'),
            'expected' => [new BaseNotIn(['key', 'other'])],
        ];
    }

    public function passwordAttributesDataProvider(): Generator
    {
        BasePassword::defaults(
            fn () => BasePassword::min(23)
                ->symbols()
                ->mixedCase()
                ->numbers()
                ->uncompromised(0)
        );

        yield [
            'attribute' => new Password(),
            'expected' => [
                BasePassword::min(23)
                    ->symbols()
                    ->mixedCase()
                    ->numbers()
                    ->uncompromised(0),
            ],
        ];

        yield [
            'attribute' => new Password(),
            'expected' => [ BasePassword::default() ],
        ];

        yield [
            'attribute' => new Password(min: 12),
            'expected' => [new BasePassword(12)],
        ];

        yield [
            'attribute' => new Password(min: 20),
            'expected' => [new BasePassword(20)],
        ];

        yield [
            'attribute' => new Password(letters: true, mixedCase: true, numbers: true, uncompromised: true, uncompromisedThreshold: 12),
            'expected' => [(new BasePassword(12))->letters()->mixedCase()->numbers()->uncompromised(12)],
        ];
    }

    public function prohibitedIfAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new ProhibitedIf('field', 'key'),
            'expected' => ['prohibited_if:field,key'],
        ];

        yield [
            'attribute' => new ProhibitedIf('field', ['key', 'other']),
            'expected' => ['prohibited_if:field,key,other'],
        ];

        yield [
            'attribute' => new ProhibitedIf('field', 'key', 'other'),
            'expected' => ['prohibited_if:field,key,other'],
        ];
    }

    public function prohibitedUnlessAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new ProhibitedUnless('field', 'key'),
            'expected' => ['prohibited_unless:field,key'],
        ];

        yield [
            'attribute' => new ProhibitedUnless('field', ['key', 'other']),
            'expected' => ['prohibited_unless:field,key,other'],
        ];

        yield [
            'attribute' => new ProhibitedUnless('field', 'key', 'other'),
            'expected' => ['prohibited_unless:field,key,other'],
        ];
    }

    public function prohibitsAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new Prohibits('key'),
            'expected' => ['prohibits:key'],
        ];

        yield [
            'attribute' => new Prohibits(['key', 'other']),
            'expected' => ['prohibits:key,other'],
        ];

        yield [
            'attribute' => new Prohibits('key', 'other'),
            'expected' => ['prohibits:key,other'],
        ];
    }

    public function requiredIfAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new RequiredIf('field', 'key'),
            'expected' => ['required_if:field,key'],
        ];

        yield [
            'attribute' => new RequiredIf('field', ['key', 'other']),
            'expected' => ['required_if:field,key,other'],
        ];

        yield [
            'attribute' => new RequiredIf('field', 'key', 'other'),
            'expected' => ['required_if:field,key,other'],
        ];
    }

    public function requiredUnlessAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new RequiredUnless('field', 'key'),
            'expected' => ['required_unless:field,key'],
        ];

        yield [
            'attribute' => new RequiredUnless('field', ['key', 'other']),
            'expected' => ['required_unless:field,key,other'],
        ];

        yield [
            'attribute' => new RequiredUnless('field', 'key', 'other'),
            'expected' => ['required_unless:field,key,other'],
        ];
    }

    public function requiredWithAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new RequiredWith('key'),
            'expected' => ['required_with:key'],
        ];

        yield [
            'attribute' => new RequiredWith(['key', 'other']),
            'expected' => ['required_with:key,other'],
        ];

        yield [
            'attribute' => new RequiredWith('key', 'other'),
            'expected' => ['required_with:key,other'],
        ];
    }

    public function requiredWithAllAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new RequiredWithAll('key'),
            'expected' => ['required_with_all:key'],
        ];

        yield [
            'attribute' => new RequiredWithAll(['key', 'other']),
            'expected' => ['required_with_all:key,other'],
        ];

        yield [
            'attribute' => new RequiredWithAll('key', 'other'),
            'expected' => ['required_with_all:key,other'],
        ];
    }

    public function requiredWithoutAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new RequiredWithout('key'),
            'expected' => ['required_without:key'],
        ];

        yield [
            'attribute' => new RequiredWithout(['key', 'other']),
            'expected' => ['required_without:key,other'],
        ];

        yield [
            'attribute' => new RequiredWithout('key', 'other'),
            'expected' => ['required_without:key,other'],
        ];
    }

    public function requiredWithoutAllAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new RequiredWithoutAll('key'),
            'expected' => ['required_without_all:key'],
        ];

        yield [
            'attribute' => new RequiredWithoutAll(['key', 'other']),
            'expected' => ['required_without_all:key,other'],
        ];

        yield [
            'attribute' => new RequiredWithoutAll('key', 'other'),
            'expected' => ['required_without_all:key,other'],
        ];
    }

    public function ruleAttributesDataProvider(): Generator
    {
        $laravelRule = new class () implements RuleContract {
            public function passes($attribute, $value)
            {
            }

            public function message()
            {
            }
        };

        yield [
            'attribute' => new Rule(
                'test',
                ['a', 'b', 'c'],
                'x|y',
                $laravelRule,
                new Required()
            ),
            'expected' => [
                'test',
                'a',
                'b',
                'c',
                'x',
                'y',
                $laravelRule,
                'required',
            ],
        ];
    }

    public function startsWithAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new StartsWith('x'),
            'expected' => ['starts_with:x'],
        ];

        yield [
            'attribute' => new StartsWith(['x', 'y']),
            'expected' => ['starts_with:x,y'],
        ];

        yield [
            'attribute' => new StartsWith('x', 'y'),
            'expected' => ['starts_with:x,y'],
        ];
    }

    public function uniqueAttributesDataProvider(): Generator
    {
        yield [
            'attribute' => new Unique('users'),
            'expected' => [new BaseUnique('users')],
        ];

        yield [
            'attribute' => new Unique('users', 'email'),
            'expected' => [new BaseUnique('users', 'email')],
        ];

        yield [
            'attribute' => new Unique('users', 'email', connection: 'tenant'),
            'expected' => [new BaseUnique('tenant.users', 'email')],
        ];

        yield [
            'attribute' => new Unique('users', 'email', withoutTrashed: true),
            'expected' => [(new BaseUnique('users', 'email'))->withoutTrashed()],
        ];

        yield [
            'attribute' => new Unique('users', 'email', withoutTrashed: true, deletedAtColumn: 'deleted_when'),
            'expected' => [(new BaseUnique('users', 'email'))->withoutTrashed('deleted_when')],
        ];

        yield [
            'attribute' => new Unique('users', 'email', ignore: 5),
            'expected' => [(new BaseUnique('users', 'email'))->ignore(5)],
        ];

        yield [
            'attribute' => new Unique('users', 'email', ignore: 5, ignoreColumn: 'uuid'),
            'expected' => [(new BaseUnique('users', 'email'))->ignore(5, 'uuid')],
        ];

        $closure = fn (Builder $builder) => $builder;

        yield [
            'attribute' => new Unique('users', 'email', where: $closure),
            'expected' => [(new BaseUnique('users', 'email'))->where($closure)],
        ];
    }
}
