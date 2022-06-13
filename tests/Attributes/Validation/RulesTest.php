
// Da
uses(TestCase::class);
tasets
dataset('attributes', function () {
    TestTime::freeze(Carbon::create(2020, 05, 16, 0, 0, 0));

    yield from acceptedIfAttributesDataProvider();
    yield from afterAttributesDataProvider();
    yield from afterOrEqualAttributesDataProvider();
    yield from arrayTypeAttributesDataProvider();
    yield from beforeAttributesDataProvider();
    yield from beforeOrEqualAttributesDataProvider();
    yield from betweenAttributesDataProvider();
    yield from currentPasswordAttributesDataProvider();
    yield from dateEqualsAttributesDataProvider();
    yield from dimensionsAttributesDataProvider();
    yield from distinctAttributesDataProvider();
    yield from emailAttributesDataProvider();
    yield from endsWithAttributesDataProvider();
    yield from existsAttributesDataProvider();
    yield from inAttributesDataProvider();
    yield from mimesAttributesDataProvider();
    yield from mimeTypesAttributesDataProvider();
    yield from notInAttributesDataProvider();
    yield from passwordAttributesDataProvider();
    yield from prohibitedIfAttributesDataProvider();
    yield from prohibitedUnlessAttributesDataProvider();
    yield from prohibitsAttributesDataProvider();
    yield from requiredIfAttributesDataProvider();
    yield from requiredUnlessAttributesDataProvider();
    yield from requiredWithAttributesDataProvider();
    yield from requiredWithAllAttributesDataProvider();
    yield from requiredWithoutAttributesDataProvider();
    yield from requiredWithoutAllAttributesDataProvider();
    yield from startsWithAttributesDataProvider();
    yield from uniqueAttributesDataProvider();

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
        attribute: new DateFormat('Y-m-d'),
        expected: 'date_format:Y-m-d',
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
        attribute: new Max(10),
        expected: 'max:10',
    );

    yield fixature(
        attribute: new Min(10),
        expected: 'min:10',
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
        attribute: new Url(),
        expected: 'url',
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

// Helpers
function acceptedIfAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new AcceptedIf('value', 'string'),
        expected: 'accepted_if:value,string',
    );

    yield test()->fixature(
        attribute: new AcceptedIf('value', true),
        expected: 'accepted_if:value,true',
    );

    yield test()->fixature(
        attribute: new AcceptedIf('value', 42),
        expected: 'accepted_if:value,42',
    );

    yield test()->fixature(
        attribute: new AcceptedIf('value', 3.14),
        expected: 'accepted_if:value,3.14',
    );
}

function afterAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new After('some_field'),
        expected: 'after:some_field',
    );

    yield test()->fixature(
        attribute: new After(Carbon::yesterday()),
        expected: 'after:2020-05-15T00:00:00+00:00',
    );
}

function afterOrEqualAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new AfterOrEqual('some_field'),
        expected: 'after_or_equal:some_field',
    );

    yield test()->fixature(
        attribute: new AfterOrEqual(Carbon::yesterday()),
        expected: 'after_or_equal:2020-05-15T00:00:00+00:00',
    );
}

function arrayTypeAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new ArrayType(),
        expected: 'array',
    );

    yield test()->fixature(
        attribute: new ArrayType(['a', 'b', 'c']),
        expected: 'array:a,b,c',
    );

    yield test()->fixature(
        attribute: new ArrayType('a', 'b', 'c'),
        expected: 'array:a,b,c',
    );

    yield test()->fixature(
        attribute: new ArrayType('a', ['b', 'c']),
        expected: 'array:a,b,c',
    );
}

function beforeAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new Before('some_field'),
        expected: 'before:some_field',
    );

    yield test()->fixature(
        attribute: new Before(Carbon::yesterday()),
        expected: 'before:2020-05-15T00:00:00+00:00',
    );
}

function beforeOrEqualAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new BeforeOrEqual('some_field'),
        expected: 'before_or_equal:some_field',
    );

    yield test()->fixature(
        attribute: new BeforeOrEqual(Carbon::yesterday()),
        expected: 'before_or_equal:2020-05-15T00:00:00+00:00',
    );
}

function betweenAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new Between(-42, 42),
        expected: 'between:-42,42',
    );

    yield test()->fixature(
        attribute: new Between(-3.14, 3.14),
        expected: 'between:-3.14,3.14',
    );

    yield test()->fixature(
        attribute: new Between(-3.14, 42),
        expected: 'between:-3.14,42',
    );
}

function currentPasswordAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new CurrentPassword(),
        expected: 'current_password',
    );

    yield test()->fixature(
        attribute: new CurrentPassword('api'),
        expected: 'current_password:api',
    );
}

function dateEqualsAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new DateEquals('tomorrow'),
        expected: 'date_equals:tomorrow',
    );

    yield test()->fixature(
        attribute: new DateEquals(Carbon::yesterday()),
        expected: 'date_equals:2020-05-15T00:00:00+00:00',
    );
}

function dimensionsAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new Dimensions(minWidth: 15, minHeight: 10, maxWidth: 150, maxHeight: 100, ratio: 1),
        expected: 'dimensions:min_width=15,min_height=10,max_width=150,max_height=100,ratio=1',
    );

    yield test()->fixature(
        attribute: new Dimensions(maxWidth: 150, maxHeight: 100),
        expected: 'dimensions:max_width=150,max_height=100',
    );

    yield test()->fixature(
        attribute: new Dimensions(ratio: 1.5),
        expected: 'dimensions:ratio=1.5',
    );

    yield test()->fixature(
        attribute: new Dimensions(ratio: '3/4'),
        expected: 'dimensions:ratio=3/4',
    );

//        yield test()->fixature(
//            attribute: new Dimensions(),
//            expected: '',
//            exception: CannotBuildValidationRule::class,
//        );
}

function distinctAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new Distinct(),
        expected: 'distinct',
    );

    yield test()->fixature(
        attribute: new Distinct(Distinct::Strict),
        expected: 'distinct:strict',
    );

    yield test()->fixature(
        attribute: new Distinct(Distinct::IgnoreCase),
        expected: 'distinct:ignore_case',
    );

    yield test()->fixature(
        attribute: new Distinct('fake'),
        expected: '',
        exception: CannotBuildValidationRule::class
    );
}

function emailAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new Email(),
        expected: 'email:rfc',
        expectCreatedAttribute: new Email(Email::RfcValidation),
    );

    yield test()->fixature(
        attribute: new Email(Email::RfcValidation),
        expected: 'email:rfc',
    );

    yield test()->fixature(
        attribute: new Email([Email::RfcValidation, Email::NoRfcWarningsValidation]),
        expected: 'email:rfc,strict',
    );

    yield test()->fixature(
        attribute: new Email([Email::RfcValidation, Email::NoRfcWarningsValidation, Email::DnsCheckValidation, Email::SpoofCheckValidation, Email::FilterEmailValidation]),
        expected: 'email:rfc,strict,dns,spoof,filter',
    );

    yield test()->fixature(
        attribute: new Email(Email::RfcValidation, Email::NoRfcWarningsValidation),
        expected: 'email:rfc,strict',
    );

    yield test()->fixature(
        attribute: new Email(['fake']),
        expected: '',
        exception: CannotBuildValidationRule::class,
    );
}

function endsWithAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new EndsWith('x'),
        expected: 'ends_with:x',
    );

    yield test()->fixature(
        attribute: new EndsWith(['x', 'y']),
        expected: 'ends_with:x,y',
    );

    yield test()->fixature(
        attribute: new EndsWith('x', 'y'),
        expected: 'ends_with:x,y',
    );
}

function existsAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new Exists('users'),
        expected: new BaseExists('users'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('users'))
    );

    yield test()->fixature(
        attribute: new Exists('users', 'email'),
        expected: new BaseExists('users', 'email'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('users', 'email'))
    );

    yield test()->fixature(
        attribute: new Exists('users', 'email', connection: 'tenant'),
        expected: new BaseExists('tenant.users', 'email'),
        expectCreatedAttribute: new Exists(rule: new BaseExists('tenant.users', 'email'))
    );

    $closure = fn (Builder $builder) => $builder;

    yield test()->fixature(
        attribute: new Exists('users', 'email', where: $closure),
        expected: (new BaseExists('users', 'email'))->where($closure),
        expectCreatedAttribute: new Exists(rule: (new BaseExists('users', 'email'))->where($closure))
    );
}

function inAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new In('key'),
        expected: new BaseIn(['key']),
        expectCreatedAttribute: new In(new BaseIn(['key']))
    );

    yield test()->fixature(
        attribute: new In(['key', 'other']),
        expected: new BaseIn(['key', 'other']),
        expectCreatedAttribute: new In(new BaseIn(['key', 'other']))
    );

    yield test()->fixature(
        attribute: new In('key', 'other'),
        expected: new BaseIn(['key', 'other']),
        expectCreatedAttribute: new In(new BaseIn(['key', 'other']))
    );
}

function mimesAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new MimeTypes('video/quicktime'),
        expected: 'mimestypes:video/quicktime',
    );

    yield test()->fixature(
        attribute: new MimeTypes(['video/quicktime', 'video/avi']),
        expected: 'mimestypes:video/quicktime,video/avi',
    );

    yield test()->fixature(
        attribute: new MimeTypes('video/quicktime', 'video/avi'),
        expected: 'mimestypes:video/quicktime,video/avi',
    );
}

function mimeTypesAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new Mimes('jpg'),
        expected: 'mimes:jpg',
    );

    yield test()->fixature(
        attribute: new Mimes(['jpg', 'png']),
        expected: 'mimes:jpg,png',
    );

    yield test()->fixature(
        attribute: new Mimes('jpg', 'png'),
        expected: 'mimes:jpg,png',
    );
}

function notInAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new NotIn('key'),
        expected: new BaseNotIn(['key']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key']))
    );

    yield test()->fixature(
        attribute: new NotIn(['key', 'other']),
        expected: new BaseNotIn(['key', 'other']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key', 'other']))
    );

    yield test()->fixature(
        attribute: new NotIn('key', 'other'),
        expected: new BaseNotIn(['key', 'other']),
        expectCreatedAttribute: new NotIn(new BaseNotIn(['key', 'other']))
    );
}

function passwordAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new Password(),
        expected: new BasePassword(12),
        expectCreatedAttribute: new Password(rule: new BasePassword(12)),
    );

    yield test()->fixature(
        attribute: new Password(min: 20),
        expected: new BasePassword(20),
        expectCreatedAttribute: new Password(rule: new BasePassword(20)),
    );

    yield test()->fixature(
        attribute: new Password(letters: true, mixedCase: true, numbers: true, uncompromised: true, uncompromisedThreshold: 12),
        expected: (new BasePassword(12))->letters()->mixedCase()->numbers()->uncompromised(12),
        expectCreatedAttribute: new Password(rule: (new BasePassword(12))->letters()->mixedCase()->numbers()->uncompromised(12), ),
    );
}

function prohibitedIfAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new ProhibitedIf('field', 'key'),
        expected: 'prohibited_if:field,key',
    );

    yield test()->fixature(
        attribute: new ProhibitedIf('field', ['key', 'other']),
        expected: 'prohibited_if:field,key,other',
    );

    yield test()->fixature(
        attribute: new ProhibitedIf('field', 'key', 'other'),
        expected: 'prohibited_if:field,key,other',
    );
}

function prohibitedUnlessAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new ProhibitedUnless('field', 'key'),
        expected: 'prohibited_unless:field,key',
    );

    yield test()->fixature(
        attribute: new ProhibitedUnless('field', ['key', 'other']),
        expected: 'prohibited_unless:field,key,other',
    );

    yield test()->fixature(
        attribute: new ProhibitedUnless('field', 'key', 'other'),
        expected: 'prohibited_unless:field,key,other',
    );
}

function prohibitsAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new Prohibits('key'),
        expected: 'prohibits:key',
    );

    yield test()->fixature(
        attribute: new Prohibits(['key', 'other']),
        expected: 'prohibits:key,other',
    );

    yield test()->fixature(
        attribute: new Prohibits('key', 'other'),
        expected: 'prohibits:key,other',
    );
}

function requiredIfAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new RequiredIf('field'),
        expected: 'required_if:field',
    );

    yield test()->fixature(
        attribute: new RequiredIf('field', 'key'),
        expected: 'required_if:field,key',
    );

    yield test()->fixature(
        attribute: new RequiredIf('field', ['key', 'other']),
        expected: 'required_if:field,key,other',
    );

    yield test()->fixature(
        attribute: new RequiredIf('field', 'key', 'other'),
        expected: 'required_if:field,key,other',
    );
}

function requiredUnlessAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new RequiredUnless('field', 'key'),
        expected: 'required_unless:field,key',
    );

    yield test()->fixature(
        attribute: new RequiredUnless('field', ['key', 'other']),
        expected: 'required_unless:field,key,other',
    );

    yield test()->fixature(
        attribute: new RequiredUnless('field', 'key', 'other'),
        expected: 'required_unless:field,key,other',
    );
}

function requiredWithAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new RequiredWith('key'),
        expected: 'required_with:key',
    );

    yield test()->fixature(
        attribute: new RequiredWith(['key', 'other']),
        expected: 'required_with:key,other',
    );

    yield test()->fixature(
        attribute: new RequiredWith('key', 'other'),
        expected: 'required_with:key,other',
    );
}

function requiredWithAllAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new RequiredWithAll('key'),
        expected: 'required_with_all:key',
    );

    yield test()->fixature(
        attribute: new RequiredWithAll(['key', 'other']),
        expected: 'required_with_all:key,other',
    );

    yield test()->fixature(
        attribute: new RequiredWithAll('key', 'other'),
        expected: 'required_with_all:key,other',
    );
}

function requiredWithoutAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new RequiredWithout('key'),
        expected: 'required_without:key',
    );

    yield test()->fixature(
        attribute: new RequiredWithout(['key', 'other']),
        expected: 'required_without:key,other',
    );

    yield test()->fixature(
        attribute: new RequiredWithout('key', 'other'),
        expected: 'required_without:key,other',
    );
}

function requiredWithoutAllAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new RequiredWithoutAll('key'),
        expected: 'required_without_all:key',
    );

    yield test()->fixature(
        attribute: new RequiredWithoutAll(['key', 'other']),
        expected: 'required_without_all:key,other',
    );

    yield test()->fixature(
        attribute: new RequiredWithoutAll('key', 'other'),
        expected: 'required_without_all:key,other',
    );
}

function startsWithAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new StartsWith('x'),
        expected: 'starts_with:x',
    );

    yield test()->fixature(
        attribute: new StartsWith(['x', 'y']),
        expected: 'starts_with:x,y',
    );

    yield test()->fixature(
        attribute: new StartsWith('x', 'y'),
        expected: 'starts_with:x,y',
    );
}

function uniqueAttributesDataProvider(): Generator
{
    yield test()->fixature(
        attribute: new Unique('users'),
        expected: new BaseUnique('users'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('users'))
    );

    yield test()->fixature(
        attribute: new Unique('users', 'email'),
        expected: new BaseUnique('users', 'email'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('users', 'email'))
    );

    yield test()->fixature(
        attribute: new Unique('users', 'email', connection: 'tenant'),
        expected: new BaseUnique('tenant.users', 'email'),
        expectCreatedAttribute: new Unique(rule: new BaseUnique('tenant.users', 'email'))
    );

    yield test()->fixature(
        attribute: new Unique('users', 'email', withoutTrashed: true),
        expected: (new BaseUnique('users', 'email'))->withoutTrashed(),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->withoutTrashed())
    );

    yield test()->fixature(
        attribute: new Unique('users', 'email', withoutTrashed: true, deletedAtColumn: 'deleted_when'),
        expected: (new BaseUnique('users', 'email'))->withoutTrashed('deleted_when'),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->withoutTrashed('deleted_when'))
    );

    yield test()->fixature(
        attribute: new Unique('users', 'email', ignore: 5),
        expected: (new BaseUnique('users', 'email'))->ignore(5),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->ignore(5))
    );

    yield test()->fixature(
        attribute: new Unique('users', 'email', ignore: 5, ignoreColumn: 'uuid'),
        expected: (new BaseUnique('users', 'email'))->ignore(5, 'uuid'),
        expectCreatedAttribute: new Unique(rule: (new BaseUnique('users', 'email'))->ignore(5, 'uuid'))
    );

    $closure = fn (Builder $builder) => $builder;

    yield test()->fixature(
        attribute: new Unique('users', 'email', where: $closure),
        expected: (new BaseUnique('users', 'email'))->where($closure),
        expectCreatedAttribute: new Unique(rule:(new BaseUnique('users', 'email'))->where($closure))
    );
}

function fixature(
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
