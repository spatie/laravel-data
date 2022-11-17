---
title: Validation attributes
weight: 12
---

It is possible to validate the request before a data object is constructed. This can be done by adding validation attributes to the properties of a data object like this:

```php
class SongData extends Data
{
    public function __construct(
        #[Uuid()]
        public string $uuid,
        #[Max(15), IP, StartsWith('192.')]
        public string $ip,
    ) {
    }
}
```

## Creating your validation attribute

A validation attribute is a class that extends `ValidationRule` and returns an array of validation rules when the `getRules` method is called:

```php
#[Attribute(Attribute::TARGET_PROPERTY)]
class CustomRule extends ValidationRule
{
    public function getRules(): array
    {
        return [new CustomRule()];
    }
}
```

## Available validation attributes

### Accepted

[Docs](https://laravel.com/docs/9.x/validation#rule-accepted)

```php
#[Accepted]
public bool $value; 
```

### AcceptedIf

[Docs](https://laravel.com/docs/9.x/validation#rule-accepted-if)

```php
#[AcceptedIf('other_field', 'equals_this')]
public bool $value; 
```

### ActiveUrl

[Docs](https://laravel.com/docs/9.x/validation#rule-active-url)

```php
#[ActiveUrl]
public string $value; 
```

### After

[Docs](https://laravel.com/docs/9.x/validation#rule-after)

```php
#[After('other_field')]
public Carbon $value; 

#[After('tomorrow')]
public Carbon $value; 

#[After(Carbon::yesterday())]
public Carbon $value; 
```

### AfterOrEqual

[Docs](https://laravel.com/docs/9.x/validation#rule-after-or-equal)

```php
#[AfterOrEqual('other_field')]
public Carbon $value; 

#[AfterOrEqual('tomorrow')]
public Carbon $value; 

#[AfterOrEqual(Carbon::yesterday())]
public Carbon $value; 
```

### Alpha

[Docs](https://laravel.com/docs/9.x/validation#rule-alpha)

```php
#[Alpha]
public string $value; 
```

### AlphaDash

[Docs](https://laravel.com/docs/9.x/validation#rule-alpha-dash)

```php
#[AlphaDash]
public string $value; 
```

### AlphaNumeric

[Docs](https://laravel.com/docs/9.x/validation#rule-alpha-num)

```php
#[AlphaNumeric]
public string $value; 
```

### ArrayType

[Docs](https://laravel.com/docs/9.x/validation#rule-array)

```php
#[ArrayType]
public array $value; 

#[ArrayType(['valid_key', 'other_valid_key'])]
public array $value; 

#[ArrayType('valid_key', 'other_valid_key')]
public array $value; 
```

### Bail

[Docs](https://laravel.com/docs/9.x/validation#rule-bail)

```php
#[Bail]
public string $value; 
```

### Before

[Docs](https://laravel.com/docs/9.x/validation#rule-before)

```php
#[Before('other_field')]
public Carbon $value; 

#[Before('tomorrow')]
public Carbon $value; 

#[Before(Carbon::yesterday())]
public Carbon $value; 
```

### BeforeOrEqual

[Docs](https://laravel.com/docs/9.x/validation#rule-before-or-equal)

```php
#[BeforeOrEqual('other_field')]
public Carbon $value; 

#[BeforeOrEqual('tomorrow')]
public Carbon $value; 

#[BeforeOrEqual(Carbon::yesterday())]
public Carbon $value; 
```

### Between

[Docs](https://laravel.com/docs/9.x/validation#rule-between)

```php
#[Between(3.14, 42)]
public int $value; 
```

### BooleanType

[Docs](https://laravel.com/docs/9.x/validation#rule-boolean)

```php
#[BooleanType]
public bool $value; 
```

### Confirmed

[Docs](https://laravel.com/docs/9.x/validation#rule-confirmed)

```php
#[Confirmed]
public string $value; 
```

### CurrentPassword

[Docs](https://laravel.com/docs/9.x/validation#rule-current-password)

```php
#[CurrentPassword]
public string $value; 

#[CurrentPassword('api')]
public string $value; 
```

### Date

[Docs](https://laravel.com/docs/9.x/validation#rule-date)

```php
#[Date]
public Carbon $value; 
```

### DateEquals

[Docs](https://laravel.com/docs/9.x/validation#rule-date-equals)

```php
#[DateEquals('tomorrow')]
public Carbon $value; 

#[DateEquals(Carbon::yesterday())]
public Carbon $value; 
```

### DateFormat

[Docs](https://laravel.com/docs/9.x/validation#rule-date-format)

```php
#[DateFormat('d-m-Y')]
public Carbon $value; 
```

### Different

[Docs](https://laravel.com/docs/9.x/validation#rule-different)

```php
#[Different('other_field')]
public string $value; 
```

### Digits

[Docs](https://laravel.com/docs/9.x/validation#rule-digits)

```php
#[Digits(10)]
public int $value; 
```

### DigitsBetween

[Docs](https://laravel.com/docs/9.x/validation#rule-digits-between)

```php
#[DigitsBetween(2, 10)]
public int $value; 
```

### Dimensions

[Docs](https://laravel.com/docs/9.x/validation#rule-dimensions)

```php
#[Dimensions(ratio: 1.5)]
public UploadedFile $value; 

#[Dimensions(maxWidth: 100, maxHeight: 100)]
public UploadedFile $value; 
```

### Distinct

[Docs](https://laravel.com/docs/9.x/validation#rule-distinct)

```php
#[Distinct]
public string $value;

#[Distinct(Distinct::Strict)]
public string $value;  

#[Distinct(Distinct::IgnoreCase)]
public string $value;  
```

### Email

[Docs](https://laravel.com/docs/9.x/validation#rule-email)

```php
#[Email]
public string $value;

#[Email(Email::RfcValidation)]
public string $value;  

#[Email([Distinct::RfcValidation, Distinct::DnsCheckValidation])]
public string $value;  

#[Email(Distinct::RfcValidation, Distinct::DnsCheckValidation)]
public string $value;  
```

### EndsWith

[Docs](https://laravel.com/docs/9.x/validation#rule-ends-with)

```php
#[EndsWith('a')]
public string $value;

#[EndsWith(['a', 'b'])]
public string $value;

#[EndsWith('a', 'b')]
public string $value;
```

### Enum

[Docs](https://laravel.com/docs/9.x/validation#rule-enum)

```php
#[Enum(ChannelType::class)]
public string $value;
```

### ExcludeIf

[Docs](https://laravel.com/docs/9.x/validation#rule-exclude-if)

```php
#[ExcludeIf('other_field', 'has_value')]
public string $value;
```

### ExcludeUnless

[Docs](https://laravel.com/docs/9.x/validation#rule-exclude-unless)

```php
#[ExcludeUnless('other_field', 'has_value')]
public string $value;
```

### ExcludeWithout

[Docs](https://laravel.com/docs/9.x/validation#rule-exclude-without)

```php
#[ExcludeWithout('other_field')]
public string $value;
```

### Exists

[Docs](https://laravel.com/docs/9.x/validation#rule-exists)

```php
#[Exists('users')]
public string $value; 

#[Exists(User::class)]
public string $value; 

#[Exists('users', 'email')]
public string $value;

#[Exists('users', 'email', connection: 'tenant')]
public string $value;

#[Exists('users', 'email', withoutTrashed: true)]
public string $value;
```

### File

[Docs](https://laravel.com/docs/9.x/validation#rule-file)

```php
#[File]
public UploadedFile $value; 
```

### Filled

[Docs](https://laravel.com/docs/9.x/validation#rule-filled)

```php
#[Filled]
public string $value; 
```

### GreaterThan

[Docs](https://laravel.com/docs/9.x/validation#rule-gt)

```php
#[GreaterThan('other_field')]
public int $value; 
```

### GreaterThanOrEqualTo

[Docs](https://laravel.com/docs/9.x/validation#rule-gte)

```php
#[GreaterThanOrEqualTo('other_field')]
public int $value; 
```

### Image

[Docs](https://laravel.com/docs/9.x/validation#rule-image)

```php
#[Image]
public UploadedFile $value; 
```

### In

[Docs](https://laravel.com/docs/9.x/validation#rule-in)

```php
#[In([1, 2, 3, 'a', 'b'])]
public mixed $value; 

#[In(1, 2, 3, 'a', 'b')]
public mixed $value; 
```

### InArray

[Docs](https://laravel.com/docs/9.x/validation#rule-in-array)

```php
#[InArray('other_field')]
public string $value; 
```

### IntegerType

[Docs](https://laravel.com/docs/9.x/validation#rule-integer)

```php
#[IntegerType]
public int $value; 
```

### IP

[Docs](https://laravel.com/docs/9.x/validation#rule-ip)

```php
#[IP]
public string $value; 
```

### IPv4

[Docs](https://laravel.com/docs/9.x/validation#rule-ipv4)

```php
#[IPv4]
public string $value; 
```

### IPv6

[Docs](https://laravel.com/docs/9.x/validation#rule-ipv6)

```php
#[IPv6]
public string $value; 
```

### Json

[Docs](https://laravel.com/docs/9.x/validation#rule-json)

```php
#[Json]
public string $value; 
```

### LessThan

[Docs](https://laravel.com/docs/9.x/validation#rule-lt)

```php
#[LessThan('other_field')]
public int $value; 
```

### LessThanOrEqualTo

[Docs](https://laravel.com/docs/9.x/validation#rule-lte)

```php
#[LessThanOrEqualTo('other_field')]
public int $value; 
```

### Max

[Docs](https://laravel.com/docs/9.x/validation#rule-max)

```php
#[Max(20)]
public int $value; 
```

### MimeTypes

[Docs](https://laravel.com/docs/9.x/validation#rule-mimetypes)

```php
#[MimeTypes('video/quicktime')]
public UploadedFile $value; 

#[MimeTypes(['video/quicktime', 'video/avi'])]
public UploadedFile $value; 

#[MimeTypes('video/quicktime', 'video/avi')]
public UploadedFile $value; 
```

### Mimes

[Docs](https://laravel.com/docs/9.x/validation#rule-mimes)

```php
#[Mimes('jpg')]
public UploadedFile $value; 

#[Mimes(['jpg', 'png'])]
public UploadedFile $value; 

#[Mimes('jpg', 'png')]
public UploadedFile $value; 
```

### Min

[Docs](https://laravel.com/docs/9.x/validation#rule-min)

```php
#[Min(20)]
public int $value; 
```

### MultipleOf

[Docs](https://laravel.com/docs/9.x/validation#rule-multiple-of)

```php
#[MultipleOf(3)]
public int $value; 
```

### NotIn

[Docs](https://laravel.com/docs/9.x/validation#rule-not-in)

```php
#[NotIn([1, 2, 3, 'a', 'b'])]
public mixed $value; 

#[NotIn(1, 2, 3, 'a', 'b')]
public mixed $value; 
```

### NotRegex

[Docs](https://laravel.com/docs/9.x/validation#rule-not-regex)

```php
#[NotRegex('/^.+$/i')]
public string $value; 
```

### Nullable

[Docs](https://laravel.com/docs/9.x/validation#rule-nullable)

```php
#[Nullable]
public ?string $value; 
```

### Numeric

[Docs](https://laravel.com/docs/9.x/validation#rule-numeric)

```php
#[Numeric]
public ?string $value; 
```

### Password

[Docs](https://laravel.com/docs/9.x/validation#rule-password)

```php
#[Password(min: 12, letters: true, mixedCase: true, numbers: false, symbols: false, uncompromised: true, uncompromisedThreshold: 0)]
public string $value; 
```

### Present

[Docs](https://laravel.com/docs/9.x/validation#rule-present)

```php
#[Present]
public string $value; 
```

### Prohibited

[Docs](https://laravel.com/docs/9.x/validation#rule-prohibited)

```php
#[Prohibited]
public ?string $value; 
```

### ProhibitedIf

[Docs](https://laravel.com/docs/9.x/validation#rule-prohibited-if)

```php
#[ProhibitedIf('other_field', 'has_value')]
public ?string $value; 

#[ProhibitedIf('other_field', ['has_value', 'or_this_value'])]
public ?string $value; 
```

### ProhibitedUnless

[Docs](https://laravel.com/docs/9.x/validation#rule-prohibited-unless)

```php
#[ProhibitedUnless('other_field', 'has_value')]
public ?string $value; 

#[ProhibitedUnless('other_field', ['has_value', 'or_this_value'])]
public ?string $value; 
```

### Prohibits

[Docs](https://laravel.com/docs/9.x/validation#rule-prohibits)

```php
#[Prohibits('other_field')]
public ?string $value; 

#[Prohibits(['other_field', 'another_field'])]
public ?string $value; 

#[Prohibits('other_field', 'another_field')]
public ?string $value; 
```

### Regex

[Docs](https://laravel.com/docs/9.x/validation#rule-regex)

```php
#[Regex('/^.+$/i')]
public string $value; 
```

### Required

[Docs](https://laravel.com/docs/9.x/validation#rule-required)

```php
#[Required]
public string $value; 
```

### RequiredIf

[Docs](https://laravel.com/docs/9.x/validation#rule-required-if)

```php
#[RequiredIf('other_field', 'value')]
public ?string $value; 

#[RequiredIf('other_field', ['value', 'another_value'])]
public ?string $value; 
```

### RequiredUnless

[Docs](https://laravel.com/docs/9.x/validation#rule-required-unless)

```php
#[RequiredUnless('other_field', 'value')]
public ?string $value; 

#[RequiredUnless('other_field', ['value', 'another_value'])]
public ?string $value; 
```

### RequiredWith

[Docs](https://laravel.com/docs/9.x/validation#rule-required-with)

```php
#[RequiredWith('other_field')]
public ?string $value; 

#[RequiredWith(['other_field', 'another_field'])]
public ?string $value; 

#[RequiredWith('other_field', 'another_field')]
public ?string $value; 
```

### RequiredWithAll

[Docs](https://laravel.com/docs/9.x/validation#rule-required-with-all)

```php
#[RequiredWithAll('other_field')]
public ?string $value; 

#[RequiredWithAll(['other_field', 'another_field'])]
public ?string $value; 

#[RequiredWithAll('other_field', 'another_field')]
public ?string $value; 
```

### RequiredWithout

[Docs](https://laravel.com/docs/9.x/validation#rule-required-without)

```php
#[RequiredWithout('other_field')]
public ?string $value; 

#[RequiredWithout(['other_field', 'another_field'])]
public ?string $value; 

#[RequiredWithout('other_field', 'another_field')]
public ?string $value; 
```

### RequiredWithoutAll

[Docs](https://laravel.com/docs/9.x/validation#rule-required-without-all)

```php
#[RequiredWithoutAll('other_field')]
public ?string $value; 

#[RequiredWithoutAll(['other_field', 'another_field'])]
public ?string $value; 

#[RequiredWithoutAll('other_field', 'another_field')]
public ?string $value; 
```

### Rule

```php
#[Rule('string|uuid')]
public string $value; 

#[Rule(['string','uuid'])]
public string $value; 
```

### Same

[Docs](https://laravel.com/docs/9.x/validation#rule-same)

```php
#[Same('other_field')]
public string $value; 
```

### Size

[Docs](https://laravel.com/docs/9.x/validation#rule-size)

```php
#[Size(10)]
public string $value; 
```

### Sometimes

[Docs](https://laravel.com/docs/9.x/validation#validating-when-present)

```php
#[Sometimes]
public string $value; 
```

### StartsWith

[Docs](https://laravel.com/docs/9.x/validation#rule-starts-with)

```php
#[StartsWith('a')]
public string $value;

#[StartsWith(['a', 'b'])]
public string $value;

#[StartsWith('a', 'b')]
public string $value;
```

### StringType

[Docs](https://laravel.com/docs/9.x/validation#rule-string)

```php
#[StringType()]
public string $value; 
```

### TimeZone

[Docs](https://laravel.com/docs/9.x/validation#rule-timezone)

```php
#[TimeZone()]
public string $value; 
```

### Unique

[Docs](https://laravel.com/docs/9.x/validation#rule-unqiue)

```php
#[Unique('users')]
public string $value; 

#[Unique(User::class)]
public string $value; 

#[Unique('users', 'email')]
public string $value;

#[Unique('users', connection: 'tenant')]
public string $value;

#[Unique('users', withoutTrashed: true)]
public string $value;

#[Unique('users', ignore: 5)]
public string $value;
```

### Url

[Docs](https://laravel.com/docs/9.x/validation#rule-url)

```php
#[Url]
public string $value; 
```

### Uuid

[Docs](https://laravel.com/docs/9.x/validation#rule-uuid)

```php
#[Uuid]
public string $value; 
```
