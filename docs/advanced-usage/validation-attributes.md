---
title: Validation attributes
weight: 14
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
public bool $closure; 
```

### AcceptedIf

[Docs](https://laravel.com/docs/9.x/validation#rule-accepted-if)

```php
#[AcceptedIf('other_field', 'equals_this')]
public bool $closure; 
```

### ActiveUrl

[Docs](https://laravel.com/docs/9.x/validation#rule-active-url)

```php
#[ActiveUrl]
public string $closure; 
```

### After

[Docs](https://laravel.com/docs/9.x/validation#rule-after)

```php
#[After('other_field')]
public Carbon $closure; 

#[After('tomorrow')]
public Carbon $closure; 

#[After(Carbon::yesterday())]
public Carbon $closure; 
```

### AfterOrEqual

[Docs](https://laravel.com/docs/9.x/validation#rule-after-or-equal)

```php
#[AfterOrEqual('other_field')]
public Carbon $closure; 

#[AfterOrEqual('tomorrow')]
public Carbon $closure; 

#[AfterOrEqual(Carbon::yesterday())]
public Carbon $closure; 
```

### Alpha

[Docs](https://laravel.com/docs/9.x/validation#rule-alpha)

```php
#[Alpha]
public string $closure; 
```

### AlphaDash

[Docs](https://laravel.com/docs/9.x/validation#rule-alpha-dash)

```php
#[AlphaDash]
public string $closure; 
```

### AlphaNumeric

[Docs](https://laravel.com/docs/9.x/validation#rule-alpha-num)

```php
#[AlphaNumeric]
public string $closure; 
```

### ArrayType

[Docs](https://laravel.com/docs/9.x/validation#rule-array)

```php
#[ArrayType]
public array $closure; 

#[ArrayType(['valid_key', 'other_valid_key'])]
public array $closure; 

#[ArrayType('valid_key', 'other_valid_key')]
public array $closure; 
```

### Bail

[Docs](https://laravel.com/docs/9.x/validation#rule-bail)

```php
#[Bail]
public string $closure; 
```

### Before

[Docs](https://laravel.com/docs/9.x/validation#rule-before)

```php
#[Before('other_field')]
public Carbon $closure; 

#[Before('tomorrow')]
public Carbon $closure; 

#[Before(Carbon::yesterday())]
public Carbon $closure; 
```

### BeforeOrEqual

[Docs](https://laravel.com/docs/9.x/validation#rule-before-or-equal)

```php
#[BeforeOrEqual('other_field')]
public Carbon $closure; 

#[BeforeOrEqual('tomorrow')]
public Carbon $closure; 

#[BeforeOrEqual(Carbon::yesterday())]
public Carbon $closure; 
```

### Between

[Docs](https://laravel.com/docs/9.x/validation#rule-between)

```php
#[Between(3.14, 42)]
public int $closure; 
```

### BooleanType

[Docs](https://laravel.com/docs/9.x/validation#rule-boolean)

```php
#[BooleanType]
public bool $closure; 
```

### Confirmed

[Docs](https://laravel.com/docs/9.x/validation#rule-confirmed)

```php
#[Confirmed]
public string $closure; 
```

### CurrentPassword

[Docs](https://laravel.com/docs/9.x/validation#rule-current-password)

```php
#[CurrentPassword]
public string $closure; 

#[CurrentPassword('api')]
public string $closure; 
```

### Date

[Docs](https://laravel.com/docs/9.x/validation#rule-date)

```php
#[Date]
public Carbon $closure; 
```

### DateEquals

[Docs](https://laravel.com/docs/9.x/validation#rule-date-equals)

```php
#[DateEquals('tomorrow')]
public Carbon $closure; 

#[DateEquals(Carbon::yesterday())]
public Carbon $closure; 
```

### DateFormat

[Docs](https://laravel.com/docs/9.x/validation#rule-date-format)

```php
#[DateFormat('d-m-Y')]
public Carbon $closure; 
```

### Different

[Docs](https://laravel.com/docs/9.x/validation#rule-different)

```php
#[Different('other_field')]
public string $closure; 
```

### Digits

[Docs](https://laravel.com/docs/9.x/validation#rule-digits)

```php
#[Digits(10)]
public int $closure; 
```

### DigitsBetween

[Docs](https://laravel.com/docs/9.x/validation#rule-digits-between)

```php
#[DigitsBetween(2, 10)]
public int $closure; 
```

### Dimensions

[Docs](https://laravel.com/docs/9.x/validation#rule-dimensions)

```php
#[Dimensions(ratio: 1.5)]
public UploadedFile $closure; 

#[Dimensions(maxWidth: 100, maxHeight: 100)]
public UploadedFile $closure; 
```

### Distinct

[Docs](https://laravel.com/docs/9.x/validation#rule-distinct)

```php
#[Distinct]
public string $closure;

#[Distinct(Distinct::Strict)]
public string $closure;  

#[Distinct(Distinct::IgnoreCase)]
public string $closure;  
```

### Email

[Docs](https://laravel.com/docs/9.x/validation#rule-email)

```php
#[Email]
public string $closure;

#[Email(Email::RfcValidation)]
public string $closure;  

#[Email([Email::RfcValidation, Email::DnsCheckValidation])]
public string $closure;  

#[Email(Email::RfcValidation, Email::DnsCheckValidation)]
public string $closure;  
```

### EndsWith

[Docs](https://laravel.com/docs/9.x/validation#rule-ends-with)

```php
#[EndsWith('a')]
public string $closure;

#[EndsWith(['a', 'b'])]
public string $closure;

#[EndsWith('a', 'b')]
public string $closure;
```

### Enum

[Docs](https://laravel.com/docs/9.x/validation#rule-enum)

```php
#[Enum(ChannelType::class)]
public string $closure;
```

### ExcludeIf

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/9.x/validation#rule-exclude-if)

```php
#[ExcludeIf('other_field', 'has_value')]
public string $closure;
```

### ExcludeUnless

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/9.x/validation#rule-exclude-unless)

```php
#[ExcludeUnless('other_field', 'has_value')]
public string $closure;
```

### ExcludeWithout

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/9.x/validation#rule-exclude-without)

```php
#[ExcludeWithout('other_field')]
public string $closure;
```

### Exists

[Docs](https://laravel.com/docs/9.x/validation#rule-exists)

```php
#[Exists('users')]
public string $closure; 

#[Exists(User::class)]
public string $closure; 

#[Exists('users', 'email')]
public string $closure;

#[Exists('users', 'email', connection: 'tenant')]
public string $closure;

#[Exists('users', 'email', withoutTrashed: true)]
public string $closure;
```

### File

[Docs](https://laravel.com/docs/9.x/validation#rule-file)

```php
#[File]
public UploadedFile $closure; 
```

### Filled

[Docs](https://laravel.com/docs/9.x/validation#rule-filled)

```php
#[Filled]
public string $closure; 
```

### GreaterThan

[Docs](https://laravel.com/docs/9.x/validation#rule-gt)

```php
#[GreaterThan('other_field')]
public int $closure; 
```

### GreaterThanOrEqualTo

[Docs](https://laravel.com/docs/9.x/validation#rule-gte)

```php
#[GreaterThanOrEqualTo('other_field')]
public int $closure; 
```

### Image

[Docs](https://laravel.com/docs/9.x/validation#rule-image)

```php
#[Image]
public UploadedFile $closure; 
```

### In

[Docs](https://laravel.com/docs/9.x/validation#rule-in)

```php
#[In([1, 2, 3, 'a', 'b'])]
public mixed $closure; 

#[In(1, 2, 3, 'a', 'b')]
public mixed $closure; 
```

### InArray

[Docs](https://laravel.com/docs/9.x/validation#rule-in-array)

```php
#[InArray('other_field')]
public string $closure; 
```

### IntegerType

[Docs](https://laravel.com/docs/9.x/validation#rule-integer)

```php
#[IntegerType]
public int $closure; 
```

### IP

[Docs](https://laravel.com/docs/9.x/validation#rule-ip)

```php
#[IP]
public string $closure; 
```

### IPv4

[Docs](https://laravel.com/docs/9.x/validation#rule-ipv4)

```php
#[IPv4]
public string $closure; 
```

### IPv6

[Docs](https://laravel.com/docs/9.x/validation#rule-ipv6)

```php
#[IPv6]
public string $closure; 
```

### Json

[Docs](https://laravel.com/docs/9.x/validation#rule-json)

```php
#[Json]
public string $closure; 
```

### LessThan

[Docs](https://laravel.com/docs/9.x/validation#rule-lt)

```php
#[LessThan('other_field')]
public int $closure; 
```

### LessThanOrEqualTo

[Docs](https://laravel.com/docs/9.x/validation#rule-lte)

```php
#[LessThanOrEqualTo('other_field')]
public int $closure; 
```

### Max

[Docs](https://laravel.com/docs/9.x/validation#rule-max)

```php
#[Max(20)]
public int $closure; 
```

### MimeTypes

[Docs](https://laravel.com/docs/9.x/validation#rule-mimetypes)

```php
#[MimeTypes('video/quicktime')]
public UploadedFile $closure; 

#[MimeTypes(['video/quicktime', 'video/avi'])]
public UploadedFile $closure; 

#[MimeTypes('video/quicktime', 'video/avi')]
public UploadedFile $closure; 
```

### Mimes

[Docs](https://laravel.com/docs/9.x/validation#rule-mimes)

```php
#[Mimes('jpg')]
public UploadedFile $closure; 

#[Mimes(['jpg', 'png'])]
public UploadedFile $closure; 

#[Mimes('jpg', 'png')]
public UploadedFile $closure; 
```

### Min

[Docs](https://laravel.com/docs/9.x/validation#rule-min)

```php
#[Min(20)]
public int $closure; 
```

### MultipleOf

[Docs](https://laravel.com/docs/9.x/validation#rule-multiple-of)

```php
#[MultipleOf(3)]
public int $closure; 
```

### NotIn

[Docs](https://laravel.com/docs/9.x/validation#rule-not-in)

```php
#[NotIn([1, 2, 3, 'a', 'b'])]
public mixed $closure; 

#[NotIn(1, 2, 3, 'a', 'b')]
public mixed $closure; 
```

### NotRegex

[Docs](https://laravel.com/docs/9.x/validation#rule-not-regex)

```php
#[NotRegex('/^.+$/i')]
public string $closure; 
```

### Nullable

[Docs](https://laravel.com/docs/9.x/validation#rule-nullable)

```php
#[Nullable]
public ?string $closure; 
```

### Numeric

[Docs](https://laravel.com/docs/9.x/validation#rule-numeric)

```php
#[Numeric]
public ?string $closure; 
```

### Password

[Docs](https://laravel.com/docs/9.x/validation#rule-password)

```php
#[Password(min: 12, letters: true, mixedCase: true, numbers: false, symbols: false, uncompromised: true, uncompromisedThreshold: 0)]
public string $closure; 
```

### Present

[Docs](https://laravel.com/docs/9.x/validation#rule-present)

```php
#[Present]
public string $closure; 
```

### Prohibited

[Docs](https://laravel.com/docs/9.x/validation#rule-prohibited)

```php
#[Prohibited]
public ?string $closure; 
```

### ProhibitedIf

[Docs](https://laravel.com/docs/9.x/validation#rule-prohibited-if)

```php
#[ProhibitedIf('other_field', 'has_value')]
public ?string $closure; 

#[ProhibitedIf('other_field', ['has_value', 'or_this_value'])]
public ?string $closure; 
```

### ProhibitedUnless

[Docs](https://laravel.com/docs/9.x/validation#rule-prohibited-unless)

```php
#[ProhibitedUnless('other_field', 'has_value')]
public ?string $closure; 

#[ProhibitedUnless('other_field', ['has_value', 'or_this_value'])]
public ?string $closure; 
```

### Prohibits

[Docs](https://laravel.com/docs/9.x/validation#rule-prohibits)

```php
#[Prohibits('other_field')]
public ?string $closure; 

#[Prohibits(['other_field', 'another_field'])]
public ?string $closure; 

#[Prohibits('other_field', 'another_field')]
public ?string $closure; 
```

### Regex

[Docs](https://laravel.com/docs/9.x/validation#rule-regex)

```php
#[Regex('/^.+$/i')]
public string $closure; 
```

### Required

[Docs](https://laravel.com/docs/9.x/validation#rule-required)

```php
#[Required]
public string $closure; 
```

### RequiredIf

[Docs](https://laravel.com/docs/9.x/validation#rule-required-if)

```php
#[RequiredIf('other_field', 'value')]
public ?string $closure; 

#[RequiredIf('other_field', ['value', 'another_value'])]
public ?string $closure; 
```

### RequiredUnless

[Docs](https://laravel.com/docs/9.x/validation#rule-required-unless)

```php
#[RequiredUnless('other_field', 'value')]
public ?string $closure; 

#[RequiredUnless('other_field', ['value', 'another_value'])]
public ?string $closure; 
```

### RequiredWith

[Docs](https://laravel.com/docs/9.x/validation#rule-required-with)

```php
#[RequiredWith('other_field')]
public ?string $closure; 

#[RequiredWith(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWith('other_field', 'another_field')]
public ?string $closure; 
```

### RequiredWithAll

[Docs](https://laravel.com/docs/9.x/validation#rule-required-with-all)

```php
#[RequiredWithAll('other_field')]
public ?string $closure; 

#[RequiredWithAll(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWithAll('other_field', 'another_field')]
public ?string $closure; 
```

### RequiredWithout

[Docs](https://laravel.com/docs/9.x/validation#rule-required-without)

```php
#[RequiredWithout('other_field')]
public ?string $closure; 

#[RequiredWithout(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWithout('other_field', 'another_field')]
public ?string $closure; 
```

### RequiredWithoutAll

[Docs](https://laravel.com/docs/9.x/validation#rule-required-without-all)

```php
#[RequiredWithoutAll('other_field')]
public ?string $closure; 

#[RequiredWithoutAll(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWithoutAll('other_field', 'another_field')]
public ?string $closure; 
```

### Rule

```php
#[Rule('string|uuid')]
public string $closure; 

#[Rule(['string','uuid'])]
public string $closure; 
```

### Same

[Docs](https://laravel.com/docs/9.x/validation#rule-same)

```php
#[Same('other_field')]
public string $closure; 
```

### Size

[Docs](https://laravel.com/docs/9.x/validation#rule-size)

```php
#[Size(10)]
public string $closure; 
```

### Sometimes

[Docs](https://laravel.com/docs/9.x/validation#validating-when-present)

```php
#[Sometimes]
public string $closure; 
```

### StartsWith

[Docs](https://laravel.com/docs/9.x/validation#rule-starts-with)

```php
#[StartsWith('a')]
public string $closure;

#[StartsWith(['a', 'b'])]
public string $closure;

#[StartsWith('a', 'b')]
public string $closure;
```

### StringType

[Docs](https://laravel.com/docs/9.x/validation#rule-string)

```php
#[StringType()]
public string $closure; 
```

### TimeZone

[Docs](https://laravel.com/docs/9.x/validation#rule-timezone)

```php
#[TimeZone()]
public string $closure; 
```

### Unique

[Docs](https://laravel.com/docs/9.x/validation#rule-unqiue)

```php
#[Unique('users')]
public string $closure; 

#[Unique(User::class)]
public string $closure; 

#[Unique('users', 'email')]
public string $closure;

#[Unique('users', connection: 'tenant')]
public string $closure;

#[Unique('users', withoutTrashed: true)]
public string $closure;

#[Unique('users', ignore: 5)]
public string $closure;
```

### Url

[Docs](https://laravel.com/docs/9.x/validation#rule-url)

```php
#[Url]
public string $closure; 
```

### Uuid

[Docs](https://laravel.com/docs/9.x/validation#rule-uuid)

```php
#[Uuid]
public string $closure; 
```
