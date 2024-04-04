<?php

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
use Spatie\LaravelData\Attributes\Validation\Declined;
use Spatie\LaravelData\Attributes\Validation\DeclinedIf;
use Spatie\LaravelData\Attributes\Validation\Different;
use Spatie\LaravelData\Attributes\Validation\Digits;
use Spatie\LaravelData\Attributes\Validation\DigitsBetween;
use Spatie\LaravelData\Attributes\Validation\Dimensions;
use Spatie\LaravelData\Attributes\Validation\Distinct;
use Spatie\LaravelData\Attributes\Validation\DoesntEndWith;
use Spatie\LaravelData\Attributes\Validation\DoesntStartWith;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\EndsWith;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\ExcludeIf;
use Spatie\LaravelData\Attributes\Validation\ExcludeUnless;
use Spatie\LaravelData\Attributes\Validation\ExcludeWith;
use Spatie\LaravelData\Attributes\Validation\ExcludeWithout;
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
use Spatie\LaravelData\Attributes\Validation\Lowercase;
use Spatie\LaravelData\Attributes\Validation\MacAddress;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\MaxDigits;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Attributes\Validation\MimeTypes;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\MinDigits;
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
use Spatie\LaravelData\Attributes\Validation\RequiredArrayKeys;
use Spatie\LaravelData\Attributes\Validation\RequiredIf;
use Spatie\LaravelData\Attributes\Validation\RequiredUnless;
use Spatie\LaravelData\Attributes\Validation\RequiredWith;
use Spatie\LaravelData\Attributes\Validation\RequiredWithAll;
use Spatie\LaravelData\Attributes\Validation\RequiredWithout;
use Spatie\LaravelData\Attributes\Validation\RequiredWithoutAll;
use Spatie\LaravelData\Attributes\Validation\Same;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StartsWith;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Timezone;
use Spatie\LaravelData\Attributes\Validation\Ulid;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\Validation\Uppercase;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;
use Spatie\LaravelData\Support\Validation\ValidationRule;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;

function fixature(
    ValidationRule $attribute,
    object|string|array $expected,
    object|string|null $expectCreatedAttribute = null,
    string $exception = null
): array {
    return [
        'attribute' => $attribute,
        'expected' => $expected,
        'expectedCreatedAttribute' => $expectCreatedAttribute ?? $attribute,
        'exception' => $exception,
    ];
}

dataset('attributes', function () {
    yield from acceptedIfAttributes();
    yield from afterAttributes();
    yield from afterOrEqualAttributes();
    yield from arrayTypeAttributes();
    yield from beforeAttributes();
    yield from beforeOrEqualAttributes();
    yield from betweenAttributes();
    yield from currentPasswordAttributes();
    yield from dateEqualsAttributes();
    yield from dateFormatAttributes();
    yield from dimensionsAttributes();
    yield from distinctAttributes();
    yield from doesntEndWithAttributes();
    yield from doesntStartWithAttributes();
    yield from declinedIfAttributes();
    yield from emailAttributes();
    yield from endsWithAttributes();
    yield from existsAttributes();
    yield from inAttributes();
    yield from mimesAttributes();
    yield from mimeTypesAttributes();
    yield from notInAttributes();
    yield from passwordAttributes();
    yield from prohibitedIfAttributes();
    yield from prohibitedUnlessAttributes();
    yield from prohibitsAttributes();
    yield from requiredArrayKeysAttributes();
    yield from requiredIfAttributes();
    yield from requiredUnlessAttributes();
    yield from requiredWithAttributes();
    yield from requiredWithAllAttributes();
    yield from requiredWithoutAttributes();
    yield from requiredWithoutAllAttributes();
    yield from startsWithAttributes();
    yield from uniqueAttributes();

    yield fixature(
        attribute: new Accepted(),
        expected: 'accepted',
    );

    yield fixature(
        attribute: new ActiveUrl(),
        expected: 'active_url',
    );

    yield fixature(
        attribute: new Alpha(),
        expected: 'alpha',
    );

    yield fixature(
        attribute: new AlphaDash(),
        expected: 'alpha_dash',
    );

    yield fixature(
        attribute: new AlphaNumeric(),
        expected: 'alpha_num',
    );

    yield fixature(
        attribute: new Bail(),
        expected: 'bail',
    );

    yield fixature(
        attribute: new BooleanType(),
        expected: 'boolean',
    );

    yield fixature(
        attribute: new Confirmed(),
        expected: 'confirmed',
    );

    yield fixature(
        attribute: new Date(),
        expected: 'date',
    );

    yield fixature(
        attribute: new Declined(),
        expected: 'declined',
    );


    yield fixature(
        attribute: new Different('field'),
        expected: 'different:field',
    );

    yield fixature(
        attribute: new Digits(10),
        expected: 'digits:10',
    );

    yield fixature(
        attribute: new DigitsBetween(5, 10),
        expected: 'digits_between:5,10',
    );

    yield fixature(
        attribute: new Enum('enum_class'),
        expected: new EnumRule('enum_class'),
        expectCreatedAttribute: new Enum(new EnumRule('enum_class'))
    );


    yield fixature(
        attribute: new ExcludeIf('field', true),
        expected: 'exclude_if:field,true',
        expectCreatedAttribute: new ExcludeIf('field', true)
    );

    yield fixature(
        attribute: new ExcludeUnless('field', 42),
        expected: 'exclude_unless:field,42',
    );

    yield fixature(
        attribute: new ExcludeWith('field'),
        expected: 'exclude_with:field',
    );

    yield fixature(
        attribute: new ExcludeWithout('field'),
        expected: 'exclude_without:field',
    );

    yield fixature(
        attribute: new File(),
        expected: 'file',
    );

    yield fixature(
        attribute: new Filled(),
        expected: 'filled',
    );

    yield fixature(
        attribute: new GreaterThan('field'),
        expected: 'gt:field',
    );

    yield fixature(
        attribute: new GreaterThanOrEqualTo('field'),
        expected: 'gte:field',
    );

    yield fixature(
        attribute: new Image(),
        expected: 'image',
    );

    yield fixature(
        attribute: new InArray('field'),
        expected: 'in_array:field',
    );

    yield fixature(
        attribute: new IntegerType(),
        expected: 'integer',
    );

    yield fixature(
        attribute: new IP(),
        expected: 'ip',
    );

    yield fixature(
        attribute: new IPv4(),
        expected: 'ipv4',
    );

    yield fixature(
        attribute: new IPv6(),
        expected: 'ipv6',
    );

    yield fixature(
        attribute: new Json(),
        expected: 'json',
    );

    yield fixature(
        attribute: new LessThan('field'),
        expected: 'lt:field',
    );

    yield fixature(
        attribute: new LessThanOrEqualTo('field'),
        expected: 'lte:field',
    );

    yield fixature(
        attribute: new Lowercase(),
        expected: 'lowercase',
    );

    yield fixature(
        attribute: new MacAddress(),
        expected: 'mac_address',
    );

    yield fixature(
        attribute: new Max(10),
        expected: 'max:10',
    );

    yield fixature(
        attribute: new MaxDigits(10),
        expected: 'max_digits:10',
    );

    yield fixature(
        attribute: new Min(10),
        expected: 'min:10',
    );

    yield fixature(
        attribute: new MinDigits(10),
        expected: 'min_digits:10',
    );

    yield fixature(
        attribute: new MultipleOf(10),
        expected: 'multiple_of:10',
    );

    yield fixature(
        attribute: new NotRegex('/^.+$/i'),
        expected: 'not_regex:/^.+$/i',
    );

    yield fixature(
        attribute: new Nullable(),
        expected: 'nullable',
    );

    yield fixature(
        attribute: new Numeric(),
        expected: 'numeric',
    );

    yield fixature(
        attribute: new Present(),
        expected: 'present',
    );

    yield fixature(
        attribute: new Prohibited(),
        expected: 'prohibited',
    );

    yield fixature(
        attribute: new Regex('/^.+$/i'),
        expected: 'regex:/^.+$/i',
    );

    yield fixature(
        attribute: new Required(),
        expected: 'required',
    );

    yield fixature(
        attribute: new Same('field'),
        expected: 'same:field',
    );

    yield fixature(
        attribute: new Size(10),
        expected: 'size:10',
    );

    yield fixature(
        attribute: new StringType(),
        expected: 'string',
    );

    yield fixature(
        attribute: new Timezone(),
        expected: 'timezone',
    );

    yield fixature(
        attribute: new Uppercase(),
        expected: 'uppercase',
    );

    yield fixature(
        attribute: new Url(),
        expected: 'url',
    );

    yield fixature(
        attribute: new Ulid(),
        expected: 'ulid',
    );

    yield fixature(
        attribute: new Uuid(),
        expected: 'uuid',
    );

    yield fixature(
        attribute: new Sometimes(),
        expected: 'sometimes',
    );
});

function acceptedIfAttributes(): Generator
{
    yield fixature(
        attribute: new AcceptedIf('value', 'string'),
        expected: 'accepted_if:value,string',
    );

    yield fixature(
        attribute: new AcceptedIf('value', true),
        expected: 'accepted_if:value,true',
    );

    yield fixature(
        attribute: new AcceptedIf('value', 42),
        expected: 'accepted_if:value,42',
    );

    yield fixature(
        attribute: new AcceptedIf('value', 3.14),
        expected: 'accepted_if:value,3.14',
    );

    yield fixature(
        attribute: new AcceptedIf('value', DummyBackedEnum::FOO),
        expected: 'accepted_if:value,foo',
        expectCreatedAttribute: new AcceptedIf('value', 'foo')
    );
}

function afterAttributes(): Generator
{
    yield fixature(
        attribute: new After('some_field'),
        expected: 'after:some_field',
    );

    yield fixature(
        attribute: new After('2020-05-15 00:00:00'),
        expected: 'after:2020-05-15 00:00:00',
    );
}

function afterOrEqualAttributes(): Generator
{
    yield fixature(
        attribute: new AfterOrEqual('some_field'),
        expected: 'after_or_equal:some_field',
    );

    yield fixature(
        attribute: new AfterOrEqual('2020-05-15 00:00:00'),
        expected: 'after_or_equal:2020-05-15 00:00:00',
    );
}

function arrayTypeAttributes(): Generator
{
    yield fixature(
        attribute: new ArrayType(),
        expected: 'array',
    );

    yield fixature(
        attribute: new ArrayType(['a', 'b', 'c']),
        expected: 'array:a,b,c',
    );

    yield fixature(
        attribute: new ArrayType('a', 'b', 'c'),
        expected: 'array:a,b,c',
    );

    yield fixature(
        attribute: new ArrayType('a', ['b', 'c']),
        expected: 'array:a,b,c',
    );
}

function beforeAttributes(): Generator
{
    yield fixature(
        attribute: new Before('some_field'),
        expected: 'before:some_field',
    );

    yield fixature(
        attribute: new Before('2020-05-15 00:00:00'),
        expected: 'before:2020-05-15 00:00:00',
    );
}

function beforeOrEqualAttributes(): Generator
{
    yield fixature(
        attribute: new BeforeOrEqual('some_field'),
        expected: 'before_or_equal:some_field',
    );

    yield fixature(
        attribute: new BeforeOrEqual('2020-05-15 00:00:00'),
        expected: 'before_or_equal:2020-05-15 00:00:00',
    );
}

function betweenAttributes(): Generator
{
    yield fixature(
        attribute: new Between(-42, 42),
        expected: 'between:-42,42',
    );

    yield fixature(
        attribute: new Between(-3.14, 3.14),
        expected: 'between:-3.14,3.14',
    );

    yield fixature(
        attribute: new Between(-3.14, 42),
        expected: 'between:-3.14,42',
    );
}

function currentPasswordAttributes(): Generator
{
    yield fixature(
        attribute: new CurrentPassword(),
        expected: 'current_password',
    );

    yield fixature(
        attribute: new CurrentPassword('api'),
        expected: 'current_password:api',
    );

    yield fixature(
        attribute: new CurrentPassword(DummyBackedEnum::FOO),
        expected: 'current_password:foo',
        expectCreatedAttribute: new CurrentPassword(DummyBackedEnum::FOO->value)
    );
}

function dateEqualsAttributes(): Generator
{
    yield fixature(
        attribute: new DateEquals('tomorrow'),
        expected: 'date_equals:tomorrow',
    );

    yield fixature(
        attribute: new DateEquals('2020-05-15 00:00:00'),
        expected: 'date_equals:2020-05-15 00:00:00',
    );
}

function dateFormatAttributes(): Generator
{
    yield fixature(
        attribute: new DateFormat('Y-m-d'),
        expected: 'date_format:Y-m-d',
    );

    yield fixature(
        attribute: new DateFormat(['Y-m-d', 'Y-m-d H:i:s']),
        expected: 'date_format:Y-m-d,Y-m-d H:i:s',
    );

    yield fixature(
        attribute: new DateFormat('Y-m-d', 'Y-m-d H:i:s'),
        expected: 'date_format:Y-m-d,Y-m-d H:i:s',
    );
}

function dimensionsAttributes(): Generator
{
    yield fixature(
        attribute: new Dimensions(minWidth: 15, minHeight: 10, maxWidth: 150, maxHeight: 100, ratio: 1),
        expected: 'dimensions:min_width=15,min_height=10,max_width=150,max_height=100,ratio=1',
    );

    yield fixature(
        attribute: new Dimensions(maxWidth: 150, maxHeight: 100),
        expected: 'dimensions:max_width=150,max_height=100',
    );

    yield fixature(
        attribute: new Dimensions(ratio: 1.5),
        expected: 'dimensions:ratio=1.5',
    );

    yield fixature(
        attribute: new Dimensions(ratio: '3/4'),
        expected: 'dimensions:ratio=3/4',
    );

    //    yield fixature(
    //      attribute: new Dimensions(),
    //      expected: '',
    //      exception: CannotBuildValidationRule::class,
    //    );
}

function distinctAttributes(): Generator
{
    yield fixature(
        attribute: new Distinct(),
        expected: 'distinct',
    );

    yield fixature(
        attribute: new Distinct(Distinct::Strict),
        expected: 'distinct:strict',
    );

    yield fixature(
        attribute: new Distinct(Distinct::IgnoreCase),
        expected: 'distinct:ignore_case',
    );

    yield fixature(
        attribute: new Distinct('fake'),
        expected: '',
        exception: CannotBuildValidationRule::class
    );
}

function doesntEndWithAttributes(): Generator
{
    yield fixature(
        attribute: new DoesntEndWith('x'),
        expected: 'doesnt_end_with:x',
    );

    yield fixature(
        attribute: new DoesntEndWith(['x', 'y']),
        expected: 'doesnt_end_with:x,y',
    );

    yield fixature(
        attribute: new DoesntEndWith('x', 'y'),
        expected: 'doesnt_end_with:x,y',
    );
}

function doesntStartWithAttributes(): Generator
{
    yield fixature(
        attribute: new DoesntStartWith('x'),
        expected: 'doesnt_start_with:x',
    );

    yield fixature(
        attribute: new DoesntStartWith(['x', 'y']),
        expected: 'doesnt_start_with:x,y',
    );

    yield fixature(
        attribute: new DoesntStartWith('x', 'y'),
        expected: 'doesnt_start_with:x,y',
    );
}

function declinedIfAttributes(): Generator
{
    yield fixature(
        attribute: new DeclinedIf('value', 'string'),
        expected: 'declined_if:value,string',
    );

    yield fixature(
        attribute: new DeclinedIf('value', true),
        expected: 'declined_if:value,true',
    );

    yield fixature(
        attribute: new DeclinedIf('value', 42),
        expected: 'declined_if:value,42',
    );

    yield fixature(
        attribute: new DeclinedIf('value', 3.14),
        expected: 'declined_if:value,3.14',
    );

    yield fixature(
        attribute: new DeclinedIf('value', DummyBackedEnum::FOO),
        expected: 'declined_if:value,foo',
        expectCreatedAttribute: new DeclinedIf('value', 'foo')
    );
}

function emailAttributes(): Generator
{
    yield fixature(
        attribute: new Email(),
        expected: 'email:rfc',
        expectCreatedAttribute: new Email(Email::RfcValidation),
    );

    yield fixature(
        attribute: new Email(Email::RfcValidation),
        expected: 'email:rfc',
    );

    yield fixature(
        attribute: new Email([Email::RfcValidation, Email::NoRfcWarningsValidation]),
        expected: 'email:rfc,strict',
    );

    yield fixature(
        attribute: new Email([Email::RfcValidation, Email::NoRfcWarningsValidation, Email::DnsCheckValidation, Email::SpoofCheckValidation, Email::FilterEmailValidation]),
        expected: 'email:rfc,strict,dns,spoof,filter',
    );

    yield fixature(
        attribute: new Email(Email::RfcValidation, Email::NoRfcWarningsValidation),
        expected: 'email:rfc,strict',
    );

    yield fixature(
        attribute: new Email(['fake']),
        expected: '',
        exception: CannotBuildValidationRule::class,
    );
}

function endsWithAttributes(): Generator
{
    yield fixature(
        attribute: new EndsWith('x'),
        expected: 'ends_with:x',
    );

    yield fixature(
        attribute: new EndsWith(['x', 'y']),
        expected: 'ends_with:x,y',
    );

    yield fixature(
        attribute: new EndsWith('x', 'y'),
        expected: 'ends_with:x,y',
    );
}

function existsAttributes(): Generator
{
    yield fixature(
        attribute: new Exists('users'),
        expected: new BaseExists('users'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('users'))
    );

    yield fixature(
        attribute: new Exists('users', 'email'),
        expected: new BaseExists('users', 'email'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('users', 'email'))
    );

    yield fixature(
        attribute: new Exists('users', 'email', connection: 'tenant'),
        expected: new BaseExists('tenant.users', 'email'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('tenant.users', 'email'))
    );

    $closure = fn (Builder $builder) => $builder;

    yield fixature(
        attribute: new Exists('users', 'email', where: $closure),
        expected: (new BaseExists('users', 'email'))->where($closure),
        expectCreatedAttribute: new Exists(rule: (new BaseExists('users', 'email'))->where($closure))
    );

    yield fixature(
        attribute: new Exists('users', 'email', withoutTrashed: true),
        expected: (new BaseExists('users', 'email'))->withoutTrashed(),
        expectCreatedAttribute: new Exists(rule: (new BaseExists('users', 'email'))->withoutTrashed())
    );

    yield fixature(
        attribute: new Exists('users', 'email', withoutTrashed: true, deletedAtColumn: 'deleted_when'),
        expected: (new BaseExists('users', 'email'))->withoutTrashed('deleted_when'),
        expectCreatedAttribute: new Exists(rule: (new BaseExists('users', 'email'))->withoutTrashed('deleted_when'))
    );
}

function inAttributes(): Generator
{
    yield fixature(
        attribute: new In('key'),
        expected: new BaseIn(['key']),
        expectCreatedAttribute: new In(new BaseIn(['key']))
    );

    yield fixature(
        attribute: new In(['key', 'other']),
        expected: new BaseIn(['key', 'other']),
        expectCreatedAttribute: new In(new BaseIn(['key', 'other']))
    );

    yield fixature(
        attribute: new In('key', 'other'),
        expected: new BaseIn(['key', 'other']),
        expectCreatedAttribute: new In(new BaseIn(['key', 'other']))
    );
}

function mimesAttributes(): Generator
{
    yield fixature(
        attribute: new MimeTypes('video/quicktime'),
        expected: 'mimetypes:video/quicktime',
    );

    yield fixature(
        attribute: new MimeTypes(['video/quicktime', 'video/avi']),
        expected: 'mimetypes:video/quicktime,video/avi',
    );

    yield fixature(
        attribute: new MimeTypes('video/quicktime', 'video/avi'),
        expected: 'mimetypes:video/quicktime,video/avi',
    );
}

function mimeTypesAttributes(): Generator
{
    yield fixature(
        attribute: new Mimes('jpg'),
        expected: 'mimes:jpg',
    );

    yield fixature(
        attribute: new Mimes(['jpg', 'png']),
        expected: 'mimes:jpg,png',
    );

    yield fixature(
        attribute: new Mimes('jpg', 'png'),
        expected: 'mimes:jpg,png',
    );
}

function notInAttributes(): Generator
{
    yield fixature(
        attribute: new NotIn('key'),
        expected: new BaseNotIn(['key']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key']))
    );

    yield fixature(
        attribute: new NotIn(['key', 'other']),
        expected: new BaseNotIn(['key', 'other']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key', 'other']))
    );

    yield fixature(
        attribute: new NotIn('key', 'other'),
        expected: new BaseNotIn(['key', 'other']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key', 'other']))
    );
}

function passwordAttributes(): Generator
{
    yield fixature(
        attribute: new Password(),
        expected: new BasePassword(12),
        expectCreatedAttribute: new Password(rule: new BasePassword(12)),
    );

    yield fixature(
        attribute: new Password(min: 20),
        expected: new BasePassword(20),
        expectCreatedAttribute: new Password(rule: new BasePassword(20)),
    );

    yield fixature(
        attribute: new Password(letters: true, mixedCase: true, numbers: true, uncompromised: true, uncompromisedThreshold: 12),
        expected: (new BasePassword(12))->letters()->mixedCase()->numbers()->uncompromised(12),
        expectCreatedAttribute: new Password(rule: (new BasePassword(12))->letters()->mixedCase()->numbers()->uncompromised(12), ),
    );
}

function prohibitedIfAttributes(): Generator
{
    yield fixature(
        attribute: new ProhibitedIf('field', 'key'),
        expected: 'prohibited_if:field,key',
    );

    yield fixature(
        attribute: new ProhibitedIf('field', DummyBackedEnum::FOO),
        expected: 'prohibited_if:field,foo',
        expectCreatedAttribute: new ProhibitedIf('field', DummyBackedEnum::FOO->value),
    );

    yield fixature(
        attribute: new ProhibitedIf('field', ['key', 'other']),
        expected: 'prohibited_if:field,key,other',
    );

    yield fixature(
        attribute: new ProhibitedIf('field', 'key', 'other'),
        expected: 'prohibited_if:field,key,other',
    );
}

function prohibitedUnlessAttributes(): Generator
{
    yield fixature(
        attribute: new ProhibitedUnless('field', 'key'),
        expected: 'prohibited_unless:field,key',
    );

    yield fixature(
        attribute: new ProhibitedUnless('field', DummyBackedEnum::FOO),
        expected: 'prohibited_unless:field,foo',
        expectCreatedAttribute: new ProhibitedUnless('field', DummyBackedEnum::FOO->value),
    );

    yield fixature(
        attribute: new ProhibitedUnless('field', ['key', 'other']),
        expected: 'prohibited_unless:field,key,other',
    );

    yield fixature(
        attribute: new ProhibitedUnless('field', 'key', 'other'),
        expected: 'prohibited_unless:field,key,other',
    );
}

function prohibitsAttributes(): Generator
{
    yield fixature(
        attribute: new Prohibits('key'),
        expected: 'prohibits:key',
    );

    yield fixature(
        attribute: new Prohibits(['key', 'other']),
        expected: 'prohibits:key,other',
    );

    yield fixature(
        attribute: new Prohibits('key', 'other'),
        expected: 'prohibits:key,other',
    );
}

function requiredArrayKeysAttributes(): Generator
{
    yield fixature(
        attribute: new RequiredArrayKeys('x'),
        expected: 'required_array_keys:x',
    );

    yield fixature(
        attribute: new RequiredArrayKeys(['x', 'y']),
        expected: 'required_array_keys:x,y',
    );

    yield fixature(
        attribute: new RequiredArrayKeys('x', 'y'),
        expected: 'required_array_keys:x,y',
    );
}

function requiredIfAttributes(): Generator
{
    yield fixature(
        attribute: new RequiredIf('field'),
        expected: 'required_if:field',
    );

    yield fixature(
        attribute: new RequiredIf('field', 'key'),
        expected: 'required_if:field,key',
    );

    yield fixature(
        attribute: new RequiredIf('field', DummyBackedEnum::FOO),
        expected: 'required_if:field,foo',
        expectCreatedAttribute: new RequiredIf('field', DummyBackedEnum::FOO->value),
    );

    yield fixature(
        attribute: new RequiredIf('field', ['key', 'other']),
        expected: 'required_if:field,key,other',
    );

    yield fixature(
        attribute: new RequiredIf('field', 'key', 'other'),
        expected: 'required_if:field,key,other',
    );
}

function requiredUnlessAttributes(): Generator
{
    yield fixature(
        attribute: new RequiredUnless('field', 'key'),
        expected: 'required_unless:field,key',
    );

    yield fixature(
        attribute: new RequiredUnless('field', DummyBackedEnum::FOO),
        expected: 'required_unless:field,foo',
        expectCreatedAttribute: new RequiredUnless('field', DummyBackedEnum::FOO->value),
    );

    yield fixature(
        attribute: new RequiredUnless('field', ['key', 'other']),
        expected: 'required_unless:field,key,other',
    );

    yield fixature(
        attribute: new RequiredUnless('field', 'key', 'other'),
        expected: 'required_unless:field,key,other',
    );
    yield fixature(
        attribute: new RequiredUnless('key', 'null'),
        expected: 'required_unless:key,null',
    );
}

function requiredWithAttributes(): Generator
{
    yield fixature(
        attribute: new RequiredWith('key'),
        expected: 'required_with:key',
    );

    yield fixature(
        attribute: new RequiredWith(['key', 'other']),
        expected: 'required_with:key,other',
    );

    yield fixature(
        attribute: new RequiredWith('key', 'other'),
        expected: 'required_with:key,other',
    );
}

function requiredWithAllAttributes(): Generator
{
    yield fixature(
        attribute: new RequiredWithAll('key'),
        expected: 'required_with_all:key',
    );

    yield fixature(
        attribute: new RequiredWithAll(['key', 'other']),
        expected: 'required_with_all:key,other',
    );

    yield fixature(
        attribute: new RequiredWithAll('key', 'other'),
        expected: 'required_with_all:key,other',
    );
}

function requiredWithoutAttributes(): Generator
{
    yield fixature(
        attribute: new RequiredWithout('key'),
        expected: 'required_without:key',
    );

    yield fixature(
        attribute: new RequiredWithout(['key', 'other']),
        expected: 'required_without:key,other',
    );

    yield fixature(
        attribute: new RequiredWithout('key', 'other'),
        expected: 'required_without:key,other',
    );
}

function requiredWithoutAllAttributes(): Generator
{
    yield fixature(
        attribute: new RequiredWithoutAll('key'),
        expected: 'required_without_all:key',
    );

    yield fixature(
        attribute: new RequiredWithoutAll(['key', 'other']),
        expected: 'required_without_all:key,other',
    );

    yield fixature(
        attribute: new RequiredWithoutAll('key', 'other'),
        expected: 'required_without_all:key,other',
    );
}

function startsWithAttributes(): Generator
{
    yield fixature(
        attribute: new StartsWith('x'),
        expected: 'starts_with:x',
    );

    yield fixature(
        attribute: new StartsWith(['x', 'y']),
        expected: 'starts_with:x,y',
    );

    yield fixature(
        attribute: new StartsWith('x', 'y'),
        expected: 'starts_with:x,y',
    );
}

function uniqueAttributes(): Generator
{
    yield fixature(
        attribute: new Unique('users'),
        expected: new BaseUnique('users'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('users'))
    );

    yield fixature(
        attribute: new Unique('users', 'email'),
        expected: new BaseUnique('users', 'email'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('users', 'email'))
    );

    yield fixature(
        attribute: new Unique('users', 'email', connection: 'tenant'),
        expected: new BaseUnique('tenant.users', 'email'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('tenant.users', 'email'))
    );

    yield fixature(
        attribute: new Unique('users', 'email', withoutTrashed: true),
        expected: (new BaseUnique('users', 'email'))->withoutTrashed(),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->withoutTrashed())
    );

    yield fixature(
        attribute: new Unique('users', 'email', withoutTrashed: true, deletedAtColumn: 'deleted_when'),
        expected: (new BaseUnique('users', 'email'))->withoutTrashed('deleted_when'),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->withoutTrashed('deleted_when'))
    );

    yield fixature(
        attribute: new Unique('users', 'email', ignore: 5),
        expected: (new BaseUnique('users', 'email'))->ignore(5),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->ignore(5))
    );

    yield fixature(
        attribute: new Unique('users', 'email', ignore: 5, ignoreColumn: 'uuid'),
        expected: (new BaseUnique('users', 'email'))->ignore(5, 'uuid'),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->ignore(5, 'uuid'))
    );

    $closure = fn (Builder $builder) => $builder;

    yield fixature(
        attribute: new Unique('users', 'email', where: $closure),
        expected: (new BaseUnique('users', 'email'))->where($closure),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->where($closure))
    );
}
