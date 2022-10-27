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

dataset('attributes', function () {
    yield rulesFixture(
        attribute: new Accepted(),
        expected: 'accepted',
    );

    yield rulesFixture(
        attribute: new ActiveUrl(),
        expected: 'active_url',
    );

    yield rulesFixture(
        attribute: new Alpha(),
        expected: 'alpha',
    );

    yield rulesFixture(
        attribute: new AlphaDash(),
        expected: 'alpha_dash',
    );

    yield rulesFixture(
        attribute: new AlphaNumeric(),
        expected: 'alpha_num',
    );

    yield rulesFixture(
        attribute: new Bail(),
        expected: 'bail',
    );

    yield rulesFixture(
        attribute: new BooleanType(),
        expected: 'boolean',
    );

    yield rulesFixture(
        attribute: new Confirmed(),
        expected: 'confirmed',
    );

    yield rulesFixture(
        attribute: new Date(),
        expected: 'date',
    );

    yield rulesFixture(
        attribute: new DateFormat('Y-m-d'),
        expected: 'date_format:Y-m-d',
    );

    yield rulesFixture(
        attribute: new Different('field'),
        expected: 'different:field',
    );

    yield rulesFixture(
        attribute: new Digits(10),
        expected: 'digits:10',
    );

    yield rulesFixture(
        attribute: new DigitsBetween(5, 10),
        expected: 'digits_between:5,10',
    );

    yield rulesFixture(
        attribute: new Enum('enum_class'),
        expected: new EnumRule('enum_class'),
        expectCreatedAttribute: new Enum(new EnumRule('enum_class'))
    );

    yield rulesFixture(
        attribute: new ExcludeIf('field', true),
        expected: 'exclude_if:field,true',
        expectCreatedAttribute: new ExcludeIf('field', true)
    );

    yield rulesFixture(
        attribute: new ExcludeUnless('field', 42),
        expected: 'exclude_unless:field,42',
    );

    yield rulesFixture(
        attribute: new ExcludeWithout('field'),
        expected: 'exclude_without:field',
    );

    yield rulesFixture(
        attribute: new File(),
        expected: 'file',
    );

    yield rulesFixture(
        attribute: new Filled(),
        expected: 'filled',
    );

    yield rulesFixture(
        attribute: new GreaterThan('field'),
        expected: 'gt:field',
    );

    yield rulesFixture(
        attribute: new GreaterThanOrEqualTo('field'),
        expected: 'gte:field',
    );

    yield rulesFixture(
        attribute: new Image(),
        expected: 'image',
    );

    yield rulesFixture(
        attribute: new InArray('field'),
        expected: 'in_array:field',
    );

    yield rulesFixture(
        attribute: new IntegerType(),
        expected: 'integer',
    );

    yield rulesFixture(
        attribute: new IP(),
        expected: 'ip',
    );

    yield rulesFixture(
        attribute: new IPv4(),
        expected: 'ipv4',
    );

    yield rulesFixture(
        attribute: new IPv6(),
        expected: 'ipv6',
    );


    yield rulesFixture(
        attribute: new Json(),
        expected: 'json',
    );

    yield rulesFixture(
        attribute: new LessThan('field'),
        expected: 'lt:field',
    );

    yield rulesFixture(
        attribute: new LessThanOrEqualTo('field'),
        expected: 'lte:field',
    );

    yield rulesFixture(
        attribute: new Max(10),
        expected: 'max:10',
    );

    yield rulesFixture(
        attribute: new Min(10),
        expected: 'min:10',
    );

    yield rulesFixture(
        attribute: new MultipleOf(10),
        expected: 'multiple_of:10',
    );

    yield rulesFixture(
        attribute: new NotRegex('/^.+$/i'),
        expected: 'not_regex:/^.+$/i',
    );

    yield rulesFixture(
        attribute: new Nullable(),
        expected: 'nullable',
    );

    yield rulesFixture(
        attribute: new Numeric(),
        expected: 'numeric',
    );

    yield rulesFixture(
        attribute: new Present(),
        expected: 'present',
    );

    yield rulesFixture(
        attribute: new Prohibited(),
        expected: 'prohibited',
    );

    yield rulesFixture(
        attribute: new Regex('/^.+$/i'),
        expected: 'regex:/^.+$/i',
    );

    yield rulesFixture(
        attribute: new Required(),
        expected: 'required',
    );

    yield rulesFixture(
        attribute: new Same('field'),
        expected: 'same:field',
    );

    yield rulesFixture(
        attribute: new Size(10),
        expected: 'size:10',
    );

    yield rulesFixture(
        attribute: new StringType(),
        expected: 'string',
    );

    yield rulesFixture(
        attribute: new Timezone(),
        expected: 'timezone',
    );

    yield rulesFixture(
        attribute: new Url(),
        expected: 'url',
    );

    yield rulesFixture(
        attribute: new Uuid(),
        expected: 'uuid',
    );

    yield rulesFixture(
        attribute: new Sometimes(),
        expected: 'sometimes',
    );

    // Accepted if attributes

    yield rulesFixture(
        attribute: new AcceptedIf('value', 'string'),
        expected: 'accepted_if:value,string',
    );

    yield rulesFixture(
        attribute: new AcceptedIf('value', true),
        expected: 'accepted_if:value,true',
    );

    yield rulesFixture(
        attribute: new AcceptedIf('value', 42),
        expected: 'accepted_if:value,42',
    );

    yield rulesFixture(
        attribute: new AcceptedIf('value', 3.14),
        expected: 'accepted_if:value,3.14',
    );

    // After Attributes

     yield rulesFixture(
        attribute: new After('some_field'),
        expected: 'after:some_field',
    );

        yield rulesFixture(
        attribute: new After('2020-05-15 00:00:00'),
        expected: 'after:2020-05-15 00:00:00',
    );

    // After or equal attributes

         yield rulesFixture(
        attribute: new AfterOrEqual('some_field'),
        expected: 'after_or_equal:some_field',
    );

    yield rulesFixture(
        attribute: new AfterOrEqual('2020-05-15 00:00:00'),
        expected: 'after_or_equal:2020-05-15 00:00:00',
    );

    // Array type attributes

      yield rulesFixture(
        attribute: new ArrayType(),
        expected: 'array',
    );

    yield rulesFixture(
        attribute: new ArrayType(['a', 'b', 'c']),
        expected: 'array:a,b,c',
    );

    yield rulesFixture(
        attribute: new ArrayType('a', 'b', 'c'),
        expected: 'array:a,b,c',
    );

    yield rulesFixture(
        attribute: new ArrayType('a', ['b', 'c']),
        expected: 'array:a,b,c',
    );

    // Before attributes

    yield rulesFixture(
        attribute: new Before('some_field'),
        expected: 'before:some_field',
    );

    yield rulesFixture(
        attribute: new Before('2020-05-15 00:00:00'),
        expected: 'before:2020-05-15 00:00:00',
    );

    // Before or equal attributes

     yield rulesFixture(
        attribute: new BeforeOrEqual('some_field'),
        expected: 'before_or_equal:some_field',
    );

    yield rulesFixture(
        attribute: new BeforeOrEqual('2020-05-15 00:00:00'),
        expected: 'before_or_equal:2020-05-15 00:00:00',
    );

    // Between attributes

     yield rulesFixture(
        attribute: new Between(-42, 42),
        expected: 'between:-42,42',
    );

    yield rulesFixture(
        attribute: new Between(-3.14, 3.14),
        expected: 'between:-3.14,3.14',
    );

    yield rulesFixture(
        attribute: new Between(-3.14, 42),
        expected: 'between:-3.14,42',
    );

    // Current password attributes

     yield rulesFixture(
        attribute: new CurrentPassword(),
        expected: 'current_password',
    );

    yield rulesFixture(
        attribute: new CurrentPassword('api'),
        expected: 'current_password:api',
    );

    // Date equals attributes

    yield rulesFixture(
        attribute: new DateEquals('tomorrow'),
        expected: 'date_equals:tomorrow',
    );

    yield rulesFixture(
        attribute: new DateEquals('2020-05-15 00:00:00'),
        expected: 'date_equals:2020-05-15 00:00:00',
    );

    // Dimenstions attributes

    yield rulesFixture(
        attribute: new Dimensions(minWidth: 15, minHeight: 10, maxWidth: 150, maxHeight: 100, ratio: 1),
        expected: 'dimensions:min_width=15,min_height=10,max_width=150,max_height=100,ratio=1',
    );

    yield rulesFixture(
        attribute: new Dimensions(maxWidth: 150, maxHeight: 100),
        expected: 'dimensions:max_width=150,max_height=100',
    );

    yield rulesFixture(
        attribute: new Dimensions(ratio: 1.5),
        expected: 'dimensions:ratio=1.5',
    );

    yield rulesFixture(
        attribute: new Dimensions(ratio: '3/4'),
        expected: 'dimensions:ratio=3/4',
    );

    //        yield rulesFixture(
    //            attribute: new Dimensions(),
    //            expected: '',
    //            exception: CannotBuildValidationRule::class,
    //        );

    // Distinct attributes

     yield rulesFixture(
        attribute: new Distinct(),
        expected: 'distinct',
    );

    yield rulesFixture(
        attribute: new Distinct(Distinct::Strict),
        expected: 'distinct:strict',
    );

    yield rulesFixture(
        attribute: new Distinct(Distinct::IgnoreCase),
        expected: 'distinct:ignore_case',
    );

    yield rulesFixture(
        attribute: new Distinct('fake'),
        expected: '',
        exception: CannotBuildValidationRule::class
    );

    // Email attributes

    yield rulesFixture(
        attribute: new Email(),
        expected: 'email:rfc',
        expectCreatedAttribute: new Email(Email::RfcValidation),
    );

    yield rulesFixture(
        attribute: new Email(Email::RfcValidation),
        expected: 'email:rfc',
    );

    yield rulesFixture(
        attribute: new Email([Email::RfcValidation, Email::NoRfcWarningsValidation]),
        expected: 'email:rfc,strict',
    );

    yield rulesFixture(
        attribute: new Email([Email::RfcValidation, Email::NoRfcWarningsValidation, Email::DnsCheckValidation, Email::SpoofCheckValidation, Email::FilterEmailValidation]),
        expected: 'email:rfc,strict,dns,spoof,filter',
    );

    yield rulesFixture(
        attribute: new Email(Email::RfcValidation, Email::NoRfcWarningsValidation),
        expected: 'email:rfc,strict',
    );

    yield rulesFixture(
        attribute: new Email(['fake']),
        expected: '',
        exception: CannotBuildValidationRule::class,
    );

    // Ends with attributes

      yield rulesFixture(
        attribute: new EndsWith('x'),
        expected: 'ends_with:x',
    );

    yield rulesFixture(
        attribute: new EndsWith(['x', 'y']),
        expected: 'ends_with:x,y',
    );

    yield rulesFixture(
        attribute: new EndsWith('x', 'y'),
        expected: 'ends_with:x,y',
    );

    // Exists attributes

       yield rulesFixture(
        attribute: new Exists('users'),
        expected: new BaseExists('users'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('users'))
    );

    yield rulesFixture(
        attribute: new Exists('users', 'email'),
        expected: new BaseExists('users', 'email'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('users', 'email'))
    );

    yield rulesFixture(
        attribute: new Exists('users', 'email', connection: 'tenant'),
        expected: new BaseExists('tenant.users', 'email'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('tenant.users', 'email'))
    );

    $closure = fn (Builder $builder) => $builder;

    yield rulesFixture(
        attribute: new Exists('users', 'email', where: $closure),
        expected: (new BaseExists('users', 'email'))->where($closure),
        expectCreatedAttribute: new Exists(rule: (new BaseExists('users', 'email'))->where($closure))
    );

    // In attributes

    yield rulesFixture(
        attribute: new In('key'),
        expected: new BaseIn(['key']),
        expectCreatedAttribute: new In(new BaseIn(['key']))
    );

    yield rulesFixture(
        attribute: new In(['key', 'other']),
        expected: new BaseIn(['key', 'other']),
        expectCreatedAttribute: new In(new BaseIn(['key', 'other']))
    );

    yield rulesFixture(
        attribute: new In('key', 'other'),
        expected: new BaseIn(['key', 'other']),
        expectCreatedAttribute: new In(new BaseIn(['key', 'other']))
    );

    // Mime attributes

     yield rulesFixture(
        attribute: new MimeTypes('video/quicktime'),
        expected: 'mimestypes:video/quicktime',
    );

    yield rulesFixture(
        attribute: new MimeTypes(['video/quicktime', 'video/avi']),
        expected: 'mimestypes:video/quicktime,video/avi',
    );

    yield rulesFixture(
        attribute: new MimeTypes('video/quicktime', 'video/avi'),
        expected: 'mimestypes:video/quicktime,video/avi',
    );

    // Mime types attributes

     yield rulesFixture(
        attribute: new Mimes('jpg'),
        expected: 'mimes:jpg',
    );

    yield rulesFixture(
        attribute: new Mimes(['jpg', 'png']),
        expected: 'mimes:jpg,png',
    );

    yield rulesFixture(
        attribute: new Mimes('jpg', 'png'),
        expected: 'mimes:jpg,png',
    );

    // Not in attributes

     yield rulesFixture(
        attribute: new NotIn('key'),
        expected: new BaseNotIn(['key']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key']))
    );

    yield rulesFixture(
        attribute: new NotIn(['key', 'other']),
        expected: new BaseNotIn(['key', 'other']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key', 'other']))
    );

    yield rulesFixture(
        attribute: new NotIn('key', 'other'),
        expected: new BaseNotIn(['key', 'other']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key', 'other']))
    );

    // Password attributes

     yield rulesFixture(
        attribute: new Password(),
        expected: new BasePassword(12),
        expectCreatedAttribute: new Password(rule: new BasePassword(12)),
    );

    yield rulesFixture(
        attribute: new Password(min: 20),
        expected: new BasePassword(20),
        expectCreatedAttribute: new Password(rule: new BasePassword(20)),
    );

    yield rulesFixture(
        attribute: new Password(letters: true, mixedCase: true, numbers: true, uncompromised: true, uncompromisedThreshold: 12),
        expected: (new BasePassword(12))->letters()->mixedCase()->numbers()->uncompromised(12),
        expectCreatedAttribute: new Password(rule: (new BasePassword(12))->letters()->mixedCase()->numbers()->uncompromised(12),),
    );

    // Prohibited if attributes

    yield rulesFixture(
        attribute: new ProhibitedIf('field', 'key'),
        expected: 'prohibited_if:field,key',
    );

    yield rulesFixture(
        attribute: new ProhibitedIf('field', ['key', 'other']),
        expected: 'prohibited_if:field,key,other',
    );

    yield rulesFixture(
        attribute: new ProhibitedIf('field', 'key', 'other'),
        expected: 'prohibited_if:field,key,other',
    );

    // Prohibited unless attributes

     yield rulesFixture(
        attribute: new ProhibitedUnless('field', 'key'),
        expected: 'prohibited_unless:field,key',
    );

    yield rulesFixture(
        attribute: new ProhibitedUnless('field', ['key', 'other']),
        expected: 'prohibited_unless:field,key,other',
    );

    yield rulesFixture(
        attribute: new ProhibitedUnless('field', 'key', 'other'),
        expected: 'prohibited_unless:field,key,other',
    );

    // Prohibited attributes

    yield rulesFixture(
        attribute: new Prohibits('key'),
        expected: 'prohibits:key',
    );

    yield rulesFixture(
        attribute: new Prohibits(['key', 'other']),
        expected: 'prohibits:key,other',
    );

    yield rulesFixture(
        attribute: new Prohibits('key', 'other'),
        expected: 'prohibits:key,other',
    );

    // Required if attributes

      yield rulesFixture(
        attribute: new RequiredIf('field'),
        expected: 'required_if:field',
    );

    yield rulesFixture(
        attribute: new RequiredIf('field', 'key'),
        expected: 'required_if:field,key',
    );

    yield rulesFixture(
        attribute: new RequiredIf('field', ['key', 'other']),
        expected: 'required_if:field,key,other',
    );

    yield rulesFixture(
        attribute: new RequiredIf('field', 'key', 'other'),
        expected: 'required_if:field,key,other',
    );

    // Required unless attributes

     yield rulesFixture(
        attribute: new RequiredUnless('field', 'key'),
        expected: 'required_unless:field,key',
    );

    yield rulesFixture(
        attribute: new RequiredUnless('field', ['key', 'other']),
        expected: 'required_unless:field,key,other',
    );

    yield rulesFixture(
        attribute: new RequiredUnless('field', 'key', 'other'),
        expected: 'required_unless:field,key,other',
    );

    // Required with attributes

      yield rulesFixture(
        attribute: new RequiredWith('key'),
        expected: 'required_with:key',
    );

    yield rulesFixture(
        attribute: new RequiredWith(['key', 'other']),
        expected: 'required_with:key,other',
    );

    yield rulesFixture(
        attribute: new RequiredWith('key', 'other'),
        expected: 'required_with:key,other',
    );

    // Required with all attributes

    yield rulesFixture(
        attribute: new RequiredWithAll('key'),
        expected: 'required_with_all:key',
    );

    yield rulesFixture(
        attribute: new RequiredWithAll(['key', 'other']),
        expected: 'required_with_all:key,other',
    );

    yield rulesFixture(
        attribute: new RequiredWithAll('key', 'other'),
        expected: 'required_with_all:key,other',
    );

    // Required with all attributes

    yield rulesFixture(
        attribute: new RequiredWithAll('key'),
        expected: 'required_with_all:key',
    );

    yield rulesFixture(
        attribute: new RequiredWithAll(['key', 'other']),
        expected: 'required_with_all:key,other',
    );

    yield rulesFixture(
        attribute: new RequiredWithAll('key', 'other'),
        expected: 'required_with_all:key,other',
    );

    // Required without attributes

    yield rulesFixture(
        attribute: new RequiredWithout('key'),
        expected: 'required_without:key',
    );

    yield rulesFixture(
        attribute: new RequiredWithout(['key', 'other']),
        expected: 'required_without:key,other',
    );

    yield rulesFixture(
        attribute: new RequiredWithout('key', 'other'),
        expected: 'required_without:key,other',
    );

    // Required without all attributes

     yield rulesFixture(
        attribute: new RequiredWithoutAll('key'),
        expected: 'required_without_all:key',
    );

    yield rulesFixture(
        attribute: new RequiredWithoutAll(['key', 'other']),
        expected: 'required_without_all:key,other',
    );

    yield rulesFixture(
        attribute: new RequiredWithoutAll('key', 'other'),
        expected: 'required_without_all:key,other',
    );

    // Starts with attributes

       yield rulesFixture(
        attribute: new StartsWith('x'),
        expected: 'starts_with:x',
    );

    yield rulesFixture(
        attribute: new StartsWith(['x', 'y']),
        expected: 'starts_with:x,y',
    );

    yield rulesFixture(
        attribute: new StartsWith('x', 'y'),
        expected: 'starts_with:x,y',
    );

    // Unique attributes

       yield rulesFixture(
        attribute: new Unique('users'),
        expected: new BaseUnique('users'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('users'))
    );

    yield rulesFixture(
        attribute: new Unique('users', 'email'),
        expected: new BaseUnique('users', 'email'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('users', 'email'))
    );

    yield rulesFixture(
        attribute: new Unique('users', 'email', connection: 'tenant'),
        expected: new BaseUnique('tenant.users', 'email'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('tenant.users', 'email'))
    );

    yield rulesFixture(
        attribute: new Unique('users', 'email', withoutTrashed: true),
        expected: (new BaseUnique('users', 'email'))->withoutTrashed(),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->withoutTrashed())
    );

    yield rulesFixture(
        attribute: new Unique('users', 'email', withoutTrashed: true, deletedAtColumn: 'deleted_when'),
        expected: (new BaseUnique('users', 'email'))->withoutTrashed('deleted_when'),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->withoutTrashed('deleted_when'))
    );

    yield rulesFixture(
        attribute: new Unique('users', 'email', ignore: 5),
        expected: (new BaseUnique('users', 'email'))->ignore(5),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->ignore(5))
    );

    yield rulesFixture(
        attribute: new Unique('users', 'email', ignore: 5, ignoreColumn: 'uuid'),
        expected: (new BaseUnique('users', 'email'))->ignore(5, 'uuid'),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->ignore(5, 'uuid'))
    );

    $closure = fn (Builder $builder) => $builder;

    yield rulesFixture(
        attribute: new Unique('users', 'email', where: $closure),
        expected: (new BaseUnique('users', 'email'))->where($closure),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->where($closure))
    );
});