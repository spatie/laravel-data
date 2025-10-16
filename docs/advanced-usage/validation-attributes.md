---
title: Validation attributes
weight: 14
---

These are all the validation attributes currently available in laravel-data.

## Accepted

[Docs](https://laravel.com/docs/validation#rule-accepted)

```php
#[Accepted]
public bool $closure; 
```

## AcceptedIf

[Docs](https://laravel.com/docs/validation#rule-accepted-if)

```php
#[AcceptedIf('other_field', 'equals_this')]
public bool $closure; 
```

## ActiveUrl

[Docs](https://laravel.com/docs/validation#rule-active-url)

```php
#[ActiveUrl]
public string $closure; 
```

## After

[Docs](https://laravel.com/docs/validation#rule-after)

```php
#[After('tomorrow')]
public Carbon $closure; 

#[After(Carbon::yesterday())]
public Carbon $closure; 

// Always use field references when referencing other fields
#[After(new FieldReference('other_field'))]
public Carbon $closure; 
```

## AfterOrEqual

[Docs](https://laravel.com/docs/validation#rule-after-or-equal)

```php
#[AfterOrEqual('tomorrow')]
public Carbon $closure; 

#[AfterOrEqual(Carbon::yesterday())]
public Carbon $closure; 

// Always use field references when referencing other fields
#[AfterOrEqual(new FieldReference('other_field'))]
public Carbon $closure; 
```

## Alpha

[Docs](https://laravel.com/docs/validation#rule-alpha)

```php
#[Alpha]
public string $closure; 
```

## AlphaDash

[Docs](https://laravel.com/docs/validation#rule-alpha-dash)

```php
#[AlphaDash]
public string $closure; 
```

## AlphaNumeric

[Docs](https://laravel.com/docs/validation#rule-alpha-num)

```php
#[AlphaNumeric]
public string $closure; 
```

## ArrayType

[Docs](https://laravel.com/docs/validation#rule-array)

```php
#[ArrayType]
public array $closure; 

#[ArrayType(['valid_key', 'other_valid_key'])]
public array $closure; 

#[ArrayType('valid_key', 'other_valid_key')]
public array $closure; 
```

## Bail

[Docs](https://laravel.com/docs/validation#rule-bail)

```php
#[Bail]
public string $closure; 
```

## Before

[Docs](https://laravel.com/docs/validation#rule-before)

```php
#[Before('tomorrow')]
public Carbon $closure; 

#[Before(Carbon::yesterday())]
public Carbon $closure; 

// Always use field references when referencing other fields
#[Before(new FieldReference('other_field'))]
public Carbon $closure; 
```

## BeforeOrEqual

[Docs](https://laravel.com/docs/validation#rule-before-or-equal)

```php
#[BeforeOrEqual('tomorrow')]
public Carbon $closure; 

#[BeforeOrEqual(Carbon::yesterday())]
public Carbon $closure; 

// Always use field references when referencing other fields
#[BeforeOrEqual(new FieldReference('other_field'))]
public Carbon $closure; 
```

## Between

[Docs](https://laravel.com/docs/validation#rule-between)

```php
#[Between(3.14, 42)]
public int $closure; 
```

## BooleanType

[Docs](https://laravel.com/docs/validation#rule-boolean)

```php
#[BooleanType]
public bool $closure; 
```

## Confirmed

[Docs](https://laravel.com/docs/validation#rule-confirmed)

```php
#[Confirmed]
public string $closure; 
```

## CurrentPassword

[Docs](https://laravel.com/docs/validation#rule-current-password)

```php
#[CurrentPassword]
public string $closure; 

#[CurrentPassword('api')]
public string $closure; 
```

## Date

[Docs](https://laravel.com/docs/validation#rule-date)

```php
#[Date]
public Carbon $date; 
```

## DateEquals

[Docs](https://laravel.com/docs/validation#rule-date-equals)

```php
#[DateEquals('tomorrow')]
public Carbon $date; 

#[DateEquals(Carbon::yesterday())]
public Carbon $date; 
```

## DateFormat

[Docs](https://laravel.com/docs/validation#rule-date-format)

```php
#[DateFormat('d-m-Y')]
public Carbon $date;

#[DateFormat(['Y-m-d', 'Y-m-d H:i:s'])]
public Carbon $date;  
```

## Declined

[Docs](https://laravel.com/docs/validation#rule-declined)

```php
#[Declined]
public bool $closure; 
```

## DeclinedIf

[Docs](https://laravel.com/docs/validation#rule-declined-if)

```php
#[DeclinedIf('other_field', 'equals_this')]
public bool $closure; 
```

## Different

[Docs](https://laravel.com/docs/validation#rule-different)

```php
#[Different('other_field')]
public string $closure; 
```

## Digits

[Docs](https://laravel.com/docs/validation#rule-digits)

```php
#[Digits(10)]
public int $closure; 
```

## DigitsBetween

[Docs](https://laravel.com/docs/validation#rule-digits-between)

```php
#[DigitsBetween(2, 10)]
public int $closure; 
```

## Dimensions

[Docs](https://laravel.com/docs/validation#rule-dimensions)

```php
#[Dimensions(ratio: 1.5)]
public UploadedFile $closure; 

#[Dimensions(maxWidth: 100, maxHeight: 100)]
public UploadedFile $closure; 
```

## Distinct

[Docs](https://laravel.com/docs/validation#rule-distinct)

```php
#[Distinct]
public string $closure;

#[Distinct(Distinct::Strict)]
public string $closure;  

#[Distinct(Distinct::IgnoreCase)]
public string $closure;  
```

## DoesntEndWith

[Docs](https://laravel.com/docs/validation#rule-doesnt-end-with)

```php
#[DoesntEndWith('a')]
public string $closure;

#[DoesntEndWith(['a', 'b'])]
public string $closure;

#[DoesntEndWith('a', 'b')]
public string $closure;
```

## DoesntStartWith

[Docs](https://laravel.com/docs/validation#rule-doesnt-start-with)

```php
#[DoesntStartWith('a')]
public string $closure;

#[DoesntStartWith(['a', 'b'])]
public string $closure;

#[DoesntStartWith('a', 'b')]
public string $closure;
```

## Email

[Docs](https://laravel.com/docs/validation#rule-email)

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

## EndsWith

[Docs](https://laravel.com/docs/validation#rule-ends-with)

```php
#[EndsWith('a')]
public string $closure;

#[EndsWith(['a', 'b'])]
public string $closure;

#[EndsWith('a', 'b')]
public string $closure;
```

## Enum

[Docs](https://laravel.com/docs/validation#rule-enum)

```php
#[Enum(ChannelType::class)]
public string $closure;

#[Enum(ChannelType::class, only: [ChannelType::Email])]
public string $closure;

#[Enum(ChannelType::class, except: [ChannelType::Email])]
public string $closure;
```

## ExcludeIf

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/validation#rule-exclude-if)

```php
#[ExcludeIf('other_field', 'has_value')]
public string $closure;
```

## ExcludeUnless

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/validation#rule-exclude-unless)

```php
#[ExcludeUnless('other_field', 'has_value')]
public string $closure;
```

## ExcludeWith

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/validation#rule-exclude-with)

```php
#[ExcludeWith('other_field')]
public string $closure;
```

## ExcludeWithout

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/validation#rule-exclude-without)

```php
#[ExcludeWithout('other_field')]
public string $closure;
```

## Exists

[Docs](https://laravel.com/docs/validation#rule-exists)

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

## File

[Docs](https://laravel.com/docs/validation#rule-file)

```php
#[File]
public UploadedFile $closure; 
```

## Filled

[Docs](https://laravel.com/docs/validation#rule-filled)

```php
#[Filled]
public string $closure; 
```

## GreaterThan

[Docs](https://laravel.com/docs/validation#rule-gt)

```php
#[GreaterThan('other_field')]
public int $closure; 
```

## GreaterThanOrEqualTo

[Docs](https://laravel.com/docs/validation#rule-gte)

```php
#[GreaterThanOrEqualTo('other_field')]
public int $closure; 
```

## Image

[Docs](https://laravel.com/docs/validation#rule-image)

```php
#[Image]
public UploadedFile $closure; 
```

## In

[Docs](https://laravel.com/docs/validation#rule-in)

```php
#[In([1, 2, 3, 'a', 'b'])]
public mixed $closure; 

#[In(1, 2, 3, 'a', 'b')]
public mixed $closure; 
```

## InArray

[Docs](https://laravel.com/docs/validation#rule-in-array)

```php
#[InArray('other_field')]
public string $closure; 
```

## IntegerType

[Docs](https://laravel.com/docs/validation#rule-integer)

```php
#[IntegerType]
public int $closure; 
```

## IP

[Docs](https://laravel.com/docs/validation#rule-ip)

```php
#[IP]
public string $closure; 
```

## IPv4

[Docs](https://laravel.com/docs/validation#ipv4)

```php
#[IPv4]
public string $closure; 
```

## IPv6

[Docs](https://laravel.com/docs/validation#ipv6)

```php
#[IPv6]
public string $closure; 
```

## Json

[Docs](https://laravel.com/docs/validation#rule-json)

```php
#[Json]
public string $closure; 
```

## LessThan

[Docs](https://laravel.com/docs/validation#rule-lt)

```php
#[LessThan('other_field')]
public int $closure; 
```

## LessThanOrEqualTo

[Docs](https://laravel.com/docs/validation#rule-lte)

```php
#[LessThanOrEqualTo('other_field')]
public int $closure; 
```

## Lowercase

[Docs](https://laravel.com/docs/validation#rule-lowercase)

```php
#[Lowercase]
public string $closure; 
```

## ListType

[Docs](https://laravel.com/docs/validation#rule-list)

```php
#[ListType]
public array $array; 
```

## MacAddress

[Docs](https://laravel.com/docs/validation#rule-mac)

```php
#[MacAddress]
public string $closure; 
```

## Max

[Docs](https://laravel.com/docs/validation#rule-max)

```php
#[Max(20)]
public int $closure; 
```

## MaxDigits

[Docs](https://laravel.com/docs/validation#rule-max-digits)

```php
#[MaxDigits(10)]
public int $closure; 
```

## MimeTypes

[Docs](https://laravel.com/docs/validation#rule-mimetypes)

```php
#[MimeTypes('video/quicktime')]
public UploadedFile $closure; 

#[MimeTypes(['video/quicktime', 'video/avi'])]
public UploadedFile $closure; 

#[MimeTypes('video/quicktime', 'video/avi')]
public UploadedFile $closure; 
```

## Mimes

[Docs](https://laravel.com/docs/validation#rule-mimes)

```php
#[Mimes('jpg')]
public UploadedFile $closure; 

#[Mimes(['jpg', 'png'])]
public UploadedFile $closure; 

#[Mimes('jpg', 'png')]
public UploadedFile $closure; 
```

## Min

[Docs](https://laravel.com/docs/validation#rule-min)

```php
#[Min(20)]
public int $closure; 
```

## MinDigits

[Docs](https://laravel.com/docs/validation#rule-min-digits)

```php
#[MinDigits(2)]
public int $closure; 
```

## MultipleOf

[Docs](https://laravel.com/docs/validation#rule-multiple-of)

```php
#[MultipleOf(3)]
public int $closure; 
```

## NotIn

[Docs](https://laravel.com/docs/validation#rule-not-in)

```php
#[NotIn([1, 2, 3, 'a', 'b'])]
public mixed $closure; 

#[NotIn(1, 2, 3, 'a', 'b')]
public mixed $closure; 
```

## NotRegex

[Docs](https://laravel.com/docs/validation#rule-not-regex)

```php
#[NotRegex('/^.+$/i')]
public string $closure; 
```

## Nullable

[Docs](https://laravel.com/docs/validation#rule-nullable)

```php
#[Nullable]
public ?string $closure; 
```

## Numeric

[Docs](https://laravel.com/docs/validation#rule-numeric)

```php
#[Numeric]
public ?string $closure; 
```

## Password

[Docs](https://laravel.com/docs/validation#rule-password)

```php
#[Password(min: 12, letters: true, mixedCase: true, numbers: false, symbols: false, uncompromised: true, uncompromisedThreshold: 0)]
public string $closure; 
```

## Present

[Docs](https://laravel.com/docs/validation#rule-present)

```php
#[Present]
public string $closure; 
```

## Prohibited

[Docs](https://laravel.com/docs/validation#rule-prohibited)

```php
#[Prohibited]
public ?string $closure; 
```

## ProhibitedIf

[Docs](https://laravel.com/docs/validation#rule-prohibited-if)

```php
#[ProhibitedIf('other_field', 'has_value')]
public ?string $closure; 

#[ProhibitedIf('other_field', ['has_value', 'or_this_value'])]
public ?string $closure; 
```

## ProhibitedUnless

[Docs](https://laravel.com/docs/validation#rule-prohibited-unless)

```php
#[ProhibitedUnless('other_field', 'has_value')]
public ?string $closure; 

#[ProhibitedUnless('other_field', ['has_value', 'or_this_value'])]
public ?string $closure; 
```

## Prohibits

[Docs](https://laravel.com/docs/validation#rule-prohibits)

```php
#[Prohibits('other_field')]
public ?string $closure; 

#[Prohibits(['other_field', 'another_field'])]
public ?string $closure; 

#[Prohibits('other_field', 'another_field')]
public ?string $closure; 
```

## Regex

[Docs](https://laravel.com/docs/validation#rule-regex)

```php
#[Regex('/^.+$/i')]
public string $closure; 
```

## Required

[Docs](https://laravel.com/docs/validation#rule-required)

```php
#[Required]
public string $closure; 
```

## RequiredIf

[Docs](https://laravel.com/docs/validation#rule-required-if)

```php
#[RequiredIf('other_field', 'value')]
public ?string $closure; 

#[RequiredIf('other_field', ['value', 'another_value'])]
public ?string $closure; 
```

## RequiredUnless

[Docs](https://laravel.com/docs/validation#rule-required-unless)

```php
#[RequiredUnless('other_field', 'value')]
public ?string $closure; 

#[RequiredUnless('other_field', ['value', 'another_value'])]
public ?string $closure; 
```

## RequiredWith

[Docs](https://laravel.com/docs/validation#rule-required-with)

```php
#[RequiredWith('other_field')]
public ?string $closure; 

#[RequiredWith(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWith('other_field', 'another_field')]
public ?string $closure; 
```

## RequiredWithAll

[Docs](https://laravel.com/docs/validation#rule-required-with-all)

```php
#[RequiredWithAll('other_field')]
public ?string $closure; 

#[RequiredWithAll(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWithAll('other_field', 'another_field')]
public ?string $closure; 
```

## RequiredWithout

[Docs](https://laravel.com/docs/validation#rule-required-without)

```php
#[RequiredWithout('other_field')]
public ?string $closure; 

#[RequiredWithout(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWithout('other_field', 'another_field')]
public ?string $closure; 
```

## RequiredWithoutAll

[Docs](https://laravel.com/docs/validation#rule-required-without-all)

```php
#[RequiredWithoutAll('other_field')]
public ?string $closure; 

#[RequiredWithoutAll(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWithoutAll('other_field', 'another_field')]
public ?string $closure; 
```

## RequiredArrayKeys

[Docs](https://laravel.com/docs/validation#rule-required-array-keys)

```php
#[RequiredArrayKeys('a')]
public array $closure;

#[RequiredArrayKeys(['a', 'b'])]
public array $closure;

#[RequiredArrayKeys('a', 'b')]
public array $closure;
```

## Rule

```php
#[Rule('string|uuid')]
public string $closure; 

#[Rule(['string','uuid'])]
public string $closure; 
```

## Same

[Docs](https://laravel.com/docs/validation#rule-same)

```php
#[Same('other_field')]
public string $closure; 
```

## Size

[Docs](https://laravel.com/docs/validation#rule-size)

```php
#[Size(10)]
public string $closure; 
```

## Sometimes

[Docs](https://laravel.com/docs/validation#validating-when-present)

```php
#[Sometimes]
public string $closure; 
```

## StartsWith

[Docs](https://laravel.com/docs/validation#rule-starts-with)

```php
#[StartsWith('a')]
public string $closure;

#[StartsWith(['a', 'b'])]
public string $closure;

#[StartsWith('a', 'b')]
public string $closure;
```

## StringType

[Docs](https://laravel.com/docs/validation#rule-string)

```php
#[StringType()]
public string $closure; 
```

## TimeZone

[Docs](https://laravel.com/docs/validation#rule-timezone)

```php
#[TimeZone()]
public string $closure; 
```

## Unique

[Docs](https://laravel.com/docs/validation#rule-unique)

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

#[Unique('users', ignore: new AuthenticatedUserReference())]
public string $closure;

#[Unique('posts', ignore: new RouteParameterReference('post'))]
public string $closure;
```

## Uppercase

[Docs](https://laravel.com/docs/validation#rule-uppercase)

```php
#[Uppercase]
public string $closure; 
```

## Url

[Docs](https://laravel.com/docs/validation#rule-url)

```php
#[Url]
public string $closure; 
```

```php
#[Url(['http', 'https'])]
public string $closure;
```

## Ulid

[Docs](https://laravel.com/docs/validation#rule-ulid)

```php
#[Ulid]
public string $closure; 
```

## Uuid

[Docs](https://laravel.com/docs/validation#rule-uuid)

```php
#[Uuid]
public string $closure; 
```
