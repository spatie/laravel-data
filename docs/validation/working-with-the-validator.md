---
title: Working with the validator
weight: 5
---

Sometimes a more fine-grained control over the validation is required. In such case you can hook into the validator.

## Overwriting messages

It is possible to overwrite the error messages that will be returned when an error fails:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function messages(): array
    {
        return [
            'title.required' => 'A title is required',
            'artist.required' => 'An artist is required',
        ];
    }
}
```

## Overwriting attributes

In the default Laravel validation rules, you can overwrite the name of the attribute as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function attributes(): array
    {
        return [
            'title' => 'titel',
            'artist' => 'artiest',
        ];
    }
}
```

## Overwriting other validation functionality

Next to overwriting the validator, attributes and messages it is also possible to overwrite the following functionality.

The redirect when a validation failed:

```php
class SongData extends Data
{
    // ...

    public static function redirect(): string
    {
        return action(HomeController::class);
    }
}
```

Or the route which will be used to redirect after a validation failed:

```php
class SongData extends Data
{
    // ...

    public static function redirectRoute(): string
    {
        return 'home';
    }
}
```

Whether to stop validating on the first failure:

```php
class SongData extends Data
{
    // ...

    public static function stopOnFirstFailure(): bool
    {
        return true;
    }
}
```

The name of the error bag:

```php
class SongData extends Data
{
    // ...

    public static function errorBag(): string
    {
        return 'never_gonna_give_an_error_up';
    }
}
```

### Using dependencies in overwritten functionality

You can also provide dependencies to be injected in the overwritten validator functionality methods like `messages`
, `attributes`, `redirect`, `redirectRoute`, `stopOnFirstFailure`, `errorBag`:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function attributes(
        ValidationAttributesLanguageRepository $validationAttributesLanguageRepository
    ): array
    {
        return [
            'title' => $validationAttributesLanguageRepository->get('title'),
            'artist' => $validationAttributesLanguageRepository->get('artist'),
        ];
    }
}
```

## Overwriting the validator

Before validating the values, it is possible to plugin into the validator. This can be done as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $validator->errors()->add('field', 'Something is wrong with this field!');
        });
    }
}
```

Please note that this method will only be called on the root data object that is being validated, all the nested data objects and collections `withValidator` methods will not be called.
