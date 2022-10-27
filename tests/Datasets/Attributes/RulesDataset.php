<?php

use Spatie\LaravelData\Attributes\Validation\AcceptedIf;
use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Before;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\CurrentPassword;
use Spatie\LaravelData\Attributes\Validation\DateEquals;
use Spatie\LaravelData\Attributes\Validation\Dimensions;
use Spatie\LaravelData\Attributes\Validation\Distinct;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\EndsWith;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Illuminate\Validation\Rules\Exists as BaseExists;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rules\Enum as EnumRule;
use Illuminate\Validation\Rules\In as BaseIn;
use Illuminate\Validation\Rules\NotIn as BaseNotIn;
use Illuminate\Validation\Rules\Password as BasePassword;
use Illuminate\Validation\Rules\Unique as BaseUnique;
use Spatie\LaravelData\Attributes\Validation\Accepted;
use Spatie\LaravelData\Attributes\Validation\ActiveUrl;
use Spatie\LaravelData\Attributes\Validation\Alpha;
use Spatie\LaravelData\Attributes\Validation\AlphaDash;
use Spatie\LaravelData\Attributes\Validation\AlphaNumeric;
use Spatie\LaravelData\Attributes\Validation\Bail;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Different;
use Spatie\LaravelData\Attributes\Validation\Digits;
use Spatie\LaravelData\Attributes\Validation\DigitsBetween;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\ExcludeIf;
use Spatie\LaravelData\Attributes\Validation\ExcludeUnless;
use Spatie\LaravelData\Attributes\Validation\ExcludeWithout;
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
use Spatie\LaravelData\Attributes\Validation\Same;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StartsWith;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Timezone;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;
use Spatie\LaravelData\Support\Validation\ValidationRule;

function fixture(
    ValidationRule $attribute,
    object|string|array $expected,
    object|string|null $expectCreatedAttribute = null,
    string $exception = null
) {
    return [
        'attribute' => $attribute,
        'expected' => $expected,
        'expectedCreatedAttribute' => $expectCreatedAttribute ?? $attribute,
        'exception' => $exception,
    ];
}

dataset('attributes', function () {
    yield fixture(
        attribute: new Accepted(),
        expected: 'accepted',
    );

    yield fixture(
        attribute: new ActiveUrl(),
        expected: 'active_url',
    );

    yield fixture(
        attribute: new Alpha(),
        expected: 'alpha',
    );

    yield fixture(
        attribute: new AlphaDash(),
        expected: 'alpha_dash',
    );

    yield fixture(
        attribute: new AlphaNumeric(),
        expected: 'alpha_num',
    );

    yield fixture(
        attribute: new Bail(),
        expected: 'bail',
    );

    yield fixture(
        attribute: new BooleanType(),
        expected: 'boolean',
    );

    yield fixture(
        attribute: new Confirmed(),
        expected: 'confirmed',
    );

    yield fixture(
        attribute: new Date(),
        expected: 'date',
    );

    yield fixture(
        attribute: new DateFormat('Y-m-d'),
        expected: 'date_format:Y-m-d',
    );

    yield fixture(
        attribute: new Different('field'),
        expected: 'different:field',
    );

    yield fixture(
        attribute: new Digits(10),
        expected: 'digits:10',
    );

    yield fixture(
        attribute: new DigitsBetween(5, 10),
        expected: 'digits_between:5,10',
    );

    yield fixture(
        attribute: new Enum('enum_class'),
        expected: new EnumRule('enum_class'),
        expectCreatedAttribute: new Enum(new EnumRule('enum_class'))
    );

    yield fixture(
        attribute: new ExcludeIf('field', true),
        expected: 'exclude_if:field,true',
        expectCreatedAttribute: new ExcludeIf('field', true)
    );

    yield fixture(
        attribute: new ExcludeUnless('field', 42),
        expected: 'exclude_unless:field,42',
    );

    yield fixture(
        attribute: new ExcludeWithout('field'),
        expected: 'exclude_without:field',
    );

    yield fixture(
        attribute: new File(),
        expected: 'file',
    );

    yield fixture(
        attribute: new Filled(),
        expected: 'filled',
    );

    yield fixture(
        attribute: new GreaterThan('field'),
        expected: 'gt:field',
    );

    yield fixture(
        attribute: new GreaterThanOrEqualTo('field'),
        expected: 'gte:field',
    );

    yield fixture(
        attribute: new Image(),
        expected: 'image',
    );

    yield fixture(
        attribute: new InArray('field'),
        expected: 'in_array:field',
    );

    yield fixture(
        attribute: new IntegerType(),
        expected: 'integer',
    );

    yield fixture(
        attribute: new IP(),
        expected: 'ip',
    );

    yield fixture(
        attribute: new IPv4(),
        expected: 'ipv4',
    );

    yield fixture(
        attribute: new IPv6(),
        expected: 'ipv6',
    );


    yield fixture(
        attribute: new Json(),
        expected: 'json',
    );

    yield fixture(
        attribute: new LessThan('field'),
        expected: 'lt:field',
    );

    yield fixture(
        attribute: new LessThanOrEqualTo('field'),
        expected: 'lte:field',
    );

    yield fixture(
        attribute: new Max(10),
        expected: 'max:10',
    );

    yield fixture(
        attribute: new Min(10),
        expected: 'min:10',
    );

    yield fixture(
        attribute: new MultipleOf(10),
        expected: 'multiple_of:10',
    );

    yield fixture(
        attribute: new NotRegex('/^.+$/i'),
        expected: 'not_regex:/^.+$/i',
    );

    yield fixture(
        attribute: new Nullable(),
        expected: 'nullable',
    );

    yield fixture(
        attribute: new Numeric(),
        expected: 'numeric',
    );

    yield fixture(
        attribute: new Present(),
        expected: 'present',
    );

    yield fixture(
        attribute: new Prohibited(),
        expected: 'prohibited',
    );

    yield fixture(
        attribute: new Regex('/^.+$/i'),
        expected: 'regex:/^.+$/i',
    );

    yield fixture(
        attribute: new Required(),
        expected: 'required',
    );

    yield fixture(
        attribute: new Same('field'),
        expected: 'same:field',
    );

    yield fixture(
        attribute: new Size(10),
        expected: 'size:10',
    );

    yield fixture(
        attribute: new StringType(),
        expected: 'string',
    );

    yield fixture(
        attribute: new Timezone(),
        expected: 'timezone',
    );

    yield fixture(
        attribute: new Url(),
        expected: 'url',
    );

    yield fixture(
        attribute: new Uuid(),
        expected: 'uuid',
    );

    yield fixture(
        attribute: new Sometimes(),
        expected: 'sometimes',
    );

    // Accepted if attributes

    yield fixture(
        attribute: new AcceptedIf('value', 'string'),
        expected: 'accepted_if:value,string',
    );

    yield fixture(
        attribute: new AcceptedIf('value', true),
        expected: 'accepted_if:value,true',
    );

    yield fixture(
        attribute: new AcceptedIf('value', 42),
        expected: 'accepted_if:value,42',
    );

    yield fixture(
        attribute: new AcceptedIf('value', 3.14),
        expected: 'accepted_if:value,3.14',
    );

    // After Attributes

    yield fixture(
        attribute: new After('some_field'),
        expected: 'after:some_field',
    );

    yield fixture(
        attribute: new After('2020-05-15 00:00:00'),
        expected: 'after:2020-05-15 00:00:00',
    );

    // After or equal attributes

    yield fixture(
        attribute: new AfterOrEqual('some_field'),
        expected: 'after_or_equal:some_field',
    );

    yield fixture(
        attribute: new AfterOrEqual('2020-05-15 00:00:00'),
        expected: 'after_or_equal:2020-05-15 00:00:00',
    );

    // Array type attributes

    yield fixture(
        attribute: new ArrayType(),
        expected: 'array',
    );

    yield fixture(
        attribute: new ArrayType(['a', 'b', 'c']),
        expected: 'array:a,b,c',
    );

    yield fixture(
        attribute: new ArrayType('a', 'b', 'c'),
        expected: 'array:a,b,c',
    );

    yield fixture(
        attribute: new ArrayType('a', ['b', 'c']),
        expected: 'array:a,b,c',
    );

    // Before attributes

    yield fixture(
        attribute: new Before('some_field'),
        expected: 'before:some_field',
    );

    yield fixture(
        attribute: new Before('2020-05-15 00:00:00'),
        expected: 'before:2020-05-15 00:00:00',
    );

    // Before or equal attributes

    yield fixture(
        attribute: new BeforeOrEqual('some_field'),
        expected: 'before_or_equal:some_field',
    );

    yield fixture(
        attribute: new BeforeOrEqual('2020-05-15 00:00:00'),
        expected: 'before_or_equal:2020-05-15 00:00:00',
    );

    // Between attributes

    yield fixture(
        attribute: new Between(-42, 42),
        expected: 'between:-42,42',
    );

    yield fixture(
        attribute: new Between(-3.14, 3.14),
        expected: 'between:-3.14,3.14',
    );

    yield fixture(
        attribute: new Between(-3.14, 42),
        expected: 'between:-3.14,42',
    );

    // Current password attributes

    yield fixture(
        attribute: new CurrentPassword(),
        expected: 'current_password',
    );

    yield fixture(
        attribute: new CurrentPassword('api'),
        expected: 'current_password:api',
    );

    // Date equals attributes

    yield fixture(
        attribute: new DateEquals('tomorrow'),
        expected: 'date_equals:tomorrow',
    );

    yield fixture(
        attribute: new DateEquals('2020-05-15 00:00:00'),
        expected: 'date_equals:2020-05-15 00:00:00',
    );

    // Dimenstions attributes

    yield fixture(
        attribute: new Dimensions(minWidth: 15, minHeight: 10, maxWidth: 150, maxHeight: 100, ratio: 1),
        expected: 'dimensions:min_width=15,min_height=10,max_width=150,max_height=100,ratio=1',
    );

    yield fixture(
        attribute: new Dimensions(maxWidth: 150, maxHeight: 100),
        expected: 'dimensions:max_width=150,max_height=100',
    );

    yield fixture(
        attribute: new Dimensions(ratio: 1.5),
        expected: 'dimensions:ratio=1.5',
    );

    yield fixture(
        attribute: new Dimensions(ratio: '3/4'),
        expected: 'dimensions:ratio=3/4',
    );

    //        yield fixture(
    //            attribute: new Dimensions(),
    //            expected: '',
    //            exception: CannotBuildValidationRule::class,
    //        );

    // Distinct attributes

    yield fixture(
        attribute: new Distinct(),
        expected: 'distinct',
    );

    yield fixture(
        attribute: new Distinct(Distinct::Strict),
        expected: 'distinct:strict',
    );

    yield fixture(
        attribute: new Distinct(Distinct::IgnoreCase),
        expected: 'distinct:ignore_case',
    );

    yield fixture(
        attribute: new Distinct('fake'),
        expected: '',
        exception: CannotBuildValidationRule::class
    );

    // Email attributes

    yield fixture(
        attribute: new Email(),
        expected: 'email:rfc',
        expectCreatedAttribute: new Email(Email::RfcValidation),
    );

    yield fixture(
        attribute: new Email(Email::RfcValidation),
        expected: 'email:rfc',
    );

    yield fixture(
        attribute: new Email([Email::RfcValidation, Email::NoRfcWarningsValidation]),
        expected: 'email:rfc,strict',
    );

    yield fixture(
        attribute: new Email([Email::RfcValidation, Email::NoRfcWarningsValidation, Email::DnsCheckValidation, Email::SpoofCheckValidation, Email::FilterEmailValidation]),
        expected: 'email:rfc,strict,dns,spoof,filter',
    );

    yield fixture(
        attribute: new Email(Email::RfcValidation, Email::NoRfcWarningsValidation),
        expected: 'email:rfc,strict',
    );

    yield fixture(
        attribute: new Email(['fake']),
        expected: '',
        exception: CannotBuildValidationRule::class,
    );

    // Ends with attributes

    yield fixture(
        attribute: new EndsWith('x'),
        expected: 'ends_with:x',
    );

    yield fixture(
        attribute: new EndsWith(['x', 'y']),
        expected: 'ends_with:x,y',
    );

    yield fixture(
        attribute: new EndsWith('x', 'y'),
        expected: 'ends_with:x,y',
    );

    // Exists attributes

    yield fixture(
        attribute: new Exists('users'),
        expected: new BaseExists('users'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('users'))
    );

    yield fixture(
        attribute: new Exists('users', 'email'),
        expected: new BaseExists('users', 'email'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('users', 'email'))
    );

    yield fixture(
        attribute: new Exists('users', 'email', connection: 'tenant'),
        expected: new BaseExists('tenant.users', 'email'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('tenant.users', 'email'))
    );

    $closure = fn (Builder $builder) => $builder;

    yield fixture(
        attribute: new Exists('users', 'email', where: $closure),
        expected: (new BaseExists('users', 'email'))->where($closure),
        expectCreatedAttribute: new Exists(rule: (new BaseExists('users', 'email'))->where($closure))
    );

    // In attributes

    yield fixture(
        attribute: new In('key'),
        expected: new BaseIn(['key']),
        expectCreatedAttribute: new In(new BaseIn(['key']))
    );

    yield fixture(
        attribute: new In(['key', 'other']),
        expected: new BaseIn(['key', 'other']),
        expectCreatedAttribute: new In(new BaseIn(['key', 'other']))
    );

    yield fixture(
        attribute: new In('key', 'other'),
        expected: new BaseIn(['key', 'other']),
        expectCreatedAttribute: new In(new BaseIn(['key', 'other']))
    );

    // Mime attributes

    yield fixture(
        attribute: new MimeTypes('video/quicktime'),
        expected: 'mimestypes:video/quicktime',
    );

    yield fixture(
        attribute: new MimeTypes(['video/quicktime', 'video/avi']),
        expected: 'mimestypes:video/quicktime,video/avi',
    );

    yield fixture(
        attribute: new MimeTypes('video/quicktime', 'video/avi'),
        expected: 'mimestypes:video/quicktime,video/avi',
    );

    // Mime types attributes

    yield fixture(
        attribute: new Mimes('jpg'),
        expected: 'mimes:jpg',
    );

    yield fixture(
        attribute: new Mimes(['jpg', 'png']),
        expected: 'mimes:jpg,png',
    );

    yield fixture(
        attribute: new Mimes('jpg', 'png'),
        expected: 'mimes:jpg,png',
    );

    // Not in attributes

    yield fixture(
        attribute: new NotIn('key'),
        expected: new BaseNotIn(['key']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key']))
    );

    yield fixture(
        attribute: new NotIn(['key', 'other']),
        expected: new BaseNotIn(['key', 'other']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key', 'other']))
    );

    yield fixture(
        attribute: new NotIn('key', 'other'),
        expected: new BaseNotIn(['key', 'other']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key', 'other']))
    );

    // Password attributes

    yield fixture(
        attribute: new Password(),
        expected: new BasePassword(12),
        expectCreatedAttribute: new Password(rule: new BasePassword(12)),
    );

    yield fixture(
        attribute: new Password(min: 20),
        expected: new BasePassword(20),
        expectCreatedAttribute: new Password(rule: new BasePassword(20)),
    );

    yield fixture(
        attribute: new Password(letters: true, mixedCase: true, numbers: true, uncompromised: true, uncompromisedThreshold: 12),
        expected: (new BasePassword(12))->letters()->mixedCase()->numbers()->uncompromised(12),
        expectCreatedAttribute: new Password(rule: (new BasePassword(12))->letters()->mixedCase()->numbers()->uncompromised(12),),
    );

    // Prohibited if attributes

    yield fixture(
        attribute: new ProhibitedIf('field', 'key'),
        expected: 'prohibited_if:field,key',
    );

    yield fixture(
        attribute: new ProhibitedIf('field', ['key', 'other']),
        expected: 'prohibited_if:field,key,other',
    );

    yield fixture(
        attribute: new ProhibitedIf('field', 'key', 'other'),
        expected: 'prohibited_if:field,key,other',
    );

    // Prohibited unless attributes

    yield fixture(
        attribute: new ProhibitedUnless('field', 'key'),
        expected: 'prohibited_unless:field,key',
    );

    yield fixture(
        attribute: new ProhibitedUnless('field', ['key', 'other']),
        expected: 'prohibited_unless:field,key,other',
    );

    yield fixture(
        attribute: new ProhibitedUnless('field', 'key', 'other'),
        expected: 'prohibited_unless:field,key,other',
    );

    // Prohibited attributes

    yield fixture(
        attribute: new Prohibits('key'),
        expected: 'prohibits:key',
    );

    yield fixture(
        attribute: new Prohibits(['key', 'other']),
        expected: 'prohibits:key,other',
    );

    yield fixture(
        attribute: new Prohibits('key', 'other'),
        expected: 'prohibits:key,other',
    );

    // Required if attributes

    yield fixture(
        attribute: new RequiredIf('field'),
        expected: 'required_if:field',
    );

    yield fixture(
        attribute: new RequiredIf('field', 'key'),
        expected: 'required_if:field,key',
    );

    yield fixture(
        attribute: new RequiredIf('field', ['key', 'other']),
        expected: 'required_if:field,key,other',
    );

    yield fixture(
        attribute: new RequiredIf('field', 'key', 'other'),
        expected: 'required_if:field,key,other',
    );

    // Required unless attributes

    yield fixture(
        attribute: new RequiredUnless('field', 'key'),
        expected: 'required_unless:field,key',
    );

    yield fixture(
        attribute: new RequiredUnless('field', ['key', 'other']),
        expected: 'required_unless:field,key,other',
    );

    yield fixture(
        attribute: new RequiredUnless('field', 'key', 'other'),
        expected: 'required_unless:field,key,other',
    );

    // Required with attributes

    yield fixture(
        attribute: new RequiredWith('key'),
        expected: 'required_with:key',
    );

    yield fixture(
        attribute: new RequiredWith(['key', 'other']),
        expected: 'required_with:key,other',
    );

    yield fixture(
        attribute: new RequiredWith('key', 'other'),
        expected: 'required_with:key,other',
    );

    // Required with all attributes

    yield fixture(
        attribute: new RequiredWithAll('key'),
        expected: 'required_with_all:key',
    );

    yield fixture(
        attribute: new RequiredWithAll(['key', 'other']),
        expected: 'required_with_all:key,other',
    );

    yield fixture(
        attribute: new RequiredWithAll('key', 'other'),
        expected: 'required_with_all:key,other',
    );

    // Required with all attributes

    yield fixture(
        attribute: new RequiredWithAll('key'),
        expected: 'required_with_all:key',
    );

    yield fixture(
        attribute: new RequiredWithAll(['key', 'other']),
        expected: 'required_with_all:key,other',
    );

    yield fixture(
        attribute: new RequiredWithAll('key', 'other'),
        expected: 'required_with_all:key,other',
    );

    // Required without attributes

    yield fixture(
        attribute: new RequiredWithout('key'),
        expected: 'required_without:key',
    );

    yield fixture(
        attribute: new RequiredWithout(['key', 'other']),
        expected: 'required_without:key,other',
    );

    yield fixture(
        attribute: new RequiredWithout('key', 'other'),
        expected: 'required_without:key,other',
    );

    // Required without all attributes

    yield fixture(
        attribute: new RequiredWithoutAll('key'),
        expected: 'required_without_all:key',
    );

    yield fixture(
        attribute: new RequiredWithoutAll(['key', 'other']),
        expected: 'required_without_all:key,other',
    );

    yield fixture(
        attribute: new RequiredWithoutAll('key', 'other'),
        expected: 'required_without_all:key,other',
    );

    // Starts with attributes

    yield fixture(
        attribute: new StartsWith('x'),
        expected: 'starts_with:x',
    );

    yield fixture(
        attribute: new StartsWith(['x', 'y']),
        expected: 'starts_with:x,y',
    );

    yield fixture(
        attribute: new StartsWith('x', 'y'),
        expected: 'starts_with:x,y',
    );

    // Unique attributes

    yield fixture(
        attribute: new Unique('users'),
        expected: new BaseUnique('users'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('users'))
    );

    yield fixture(
        attribute: new Unique('users', 'email'),
        expected: new BaseUnique('users', 'email'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('users', 'email'))
    );

    yield fixture(
        attribute: new Unique('users', 'email', connection: 'tenant'),
        expected: new BaseUnique('tenant.users', 'email'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('tenant.users', 'email'))
    );

    yield fixture(
        attribute: new Unique('users', 'email', withoutTrashed: true),
        expected: (new BaseUnique('users', 'email'))->withoutTrashed(),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->withoutTrashed())
    );

    yield fixture(
        attribute: new Unique('users', 'email', withoutTrashed: true, deletedAtColumn: 'deleted_when'),
        expected: (new BaseUnique('users', 'email'))->withoutTrashed('deleted_when'),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->withoutTrashed('deleted_when'))
    );

    yield fixture(
        attribute: new Unique('users', 'email', ignore: 5),
        expected: (new BaseUnique('users', 'email'))->ignore(5),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->ignore(5))
    );

    yield fixture(
        attribute: new Unique('users', 'email', ignore: 5, ignoreColumn: 'uuid'),
        expected: (new BaseUnique('users', 'email'))->ignore(5, 'uuid'),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->ignore(5, 'uuid'))
    );

    $closure = fn (Builder $builder) => $builder;

    yield fixture(
        attribute: new Unique('users', 'email', where: $closure),
        expected: (new BaseUnique('users', 'email'))->where($closure),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->where($closure))
    );
});
