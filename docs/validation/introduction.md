---
title: Introduction
weight: 1
---

Laravel data, allows you to create data objects from all sorts of data. One of the most common ways to create a data object is from a request, and the data from a request cannot always be trusted.

That's why it is possible to validate the data before creating the data object. You can validate requests but also arrays and other structures.

The package will try to automatically infer validation rules from the data object, so you don't have to write them yourself. For example, a `?string` property will automatically have the `nullable` and `string` rules.

### Important notice

Validation is probably one of the coolest features of this package, but it is also the most complex one. We'll try to make it as straightforward as possible to validate data, but in the end, the Laravel validator was not written to be used in this way. So there are some limitations and quirks you should be aware of.

In a few cases it might be easier to just create a custom request class with validation rules and then call `toArray` on the request to create a data object than trying to validate the data with this package.

## When does validation happen?

Validation will always happen BEFORE a data object is created, once a data object is created, it is assumed that the data is valid.

At the moment, there isn't a way to validate data objects, so you should implement this logic yourself. We're looking into ways to make this easier in the future.

Validation runs automatically in the following cases:

- When injecting a data object somewhere and the data object gets created from the request
- When calling the `from` method on a data object with a request

On all other occasions, validation won't run automatically. You can always validate the data manually by calling the `validate` method on a data object:

```php
SongData::validate(
    ['title' => 'Never gonna give you up']
); // ValidationException will be thrown because 'artist' is missing
```

When you also want to create the object after validation was successful you can use `validateAndCreate`:

```php
SongData::validateAndCreate(
    ['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']
); // returns a SongData object
```

### Validate everything

It is possible to validate all payloads injected or passed to the `from` method by setting the `validation_strategy` config option to `Always`:

```php
'validation_strategy' => \Spatie\LaravelData\Support\Creation\ValidationStrategy::Always->value,
```

Completely disabling validation can be done by setting the `validation_strategy` config option to `Disabled`:

```php
'validation_strategy' => \Spatie\LaravelData\Support\Creation\ValidationStrategy::Disabled->value,
```

If you require a more fine-grained control over when validation should happen, you can use [data factories](/docs/laravel-data/v4/as-a-data-transfer-object/factories) to manually specify the validation strategy.

## A quick glance at the validation functionality

We've got a lot of documentation about validation and we suggest you read it all, but if you want to get a quick glance at the validation functionality, here's a quick overview:

### Auto rule inferring

The package will automatically infer validation rules from the data object. For example, for the following data class:

```php
class ArtistData extends Data{
    public function __construct(
        public string $name,
        public int $age,
        public ?string $genre,
    ) {
    }
}
```

The package will generate the following validation rules:

```php
[
    'name' => ['required', 'string'],
    'age' => ['required', 'integer'],
    'genre' => ['nullable', 'string'],
]
```

The package follows an algorithm to infer rules from the data object. You can read more about it [here](/docs/laravel-data/v4/validation/auto-rule-inferring).

### Validation attributes

It is possible to add extra rules as attributes to properties of a data object:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Max(20)]
        public string $artist,
    ) {
    }
}
```

When you provide an artist with a length of more than 20 characters, the validation will fail.

There's a complete [chapter](/docs/laravel-data/v4/validation/using-validation-attributes) dedicated to validation attributes.

### Manual rules

Sometimes you want to add rules manually, this can be done as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'artist' => ['required', 'string'],
        ];
    }
}
```

You can read more about manual rules in its [dedicated chapter](/docs/laravel-data/v4/validation/manual-rules).

### Using the container

You can resolve a data object from the container.

```php
app(SongData::class);
```

We resolve a data object from the container, its properties will already be filled by the values of the request with matching key names.
If the request contains data that is not compatible with the data object, a validation exception will be thrown.

### Working with the validator

We provide a few points where you can hook into the validation process. You can read more about it in the [dedicated chapter](/docs/laravel-data/v4/validation/working-with-the-validator).

It is for example to:

- overwrite validation messages & attributes
- overwrite the validator itself
- overwrite the redirect when validation fails
- allow stopping validation after a failure
- overwrite the error bag

### Authorizing a request

Just like with Laravel requests, it is possible to authorize an action for certain people only:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function authorize(): bool
    {
        return Auth::user()->name === 'Ruben';
    }
}
```

If the method returns `false`, then an `AuthorizationException` is thrown.

## Validation of nested data objects

When a data object is nested inside another data object, the validation rules will also be generated for that nested object.

```php
class SingleData{
    public function __construct(
        public ArtistData $artist,
        public SongData $song,
    ) {
    }
}
```

The validation rules for this class will be:

```php
[
    'artist' => ['array'],
    'artist.name' => ['required', 'string'],
    'artist.age' => ['required', 'integer'],
    'artist.genre' => ['nullable', 'string'],
    'song' => ['array'],
    'song.title' => ['required', 'string'],
    'song.artist' => ['required', 'string'],
]
```

There are a few quirky things to keep in mind when working with nested data objects, you can read all about it [here](/docs/laravel-data/v4/validation/nesting-data).

## Validation of nested data collections

Let's say we want to create a data object like this from a request:

```php
class AlbumData extends Data
{
    /**
    * @param array<SongData> $songs
    */
    public function __construct(
        public string $title,
        public array $songs,
    ) {
    }
}
```

Since the `SongData` has its own validation rules, the package will automatically apply them when resolving validation
rules for this object.

In this case the validation rules for `AlbumData` would look like this:

```php
[
    'title' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'songs.*.title' => ['required', 'string'],
    'songs.*.artist' => ['required', 'string'],
]
```

More info about nested data collections can be found [here](/docs/laravel-data/v4/validation/nesting-data).

## Default values

When you've set some default values for a data object, the validation rules will only be generated if something else than the default is provided.

For example, when we have this data object:

```php
class SongData extends Data
{
    public function __construct(
        public string $title = 'Never Gonna Give You Up',
        public string $artist = 'Rick Astley',
    ) {
    }
}
```

And we try to validate the following data:

```php
SongData::validate(
    ['title' => 'Giving Up On Love']
);
```

Then the validation rules will be:

```php
[
    'title' => ['required', 'string'],
]
```

## Mapping property names

When mapping property names, the validation rules will be generated for the mapped property name:

```php
class SongData extends Data
{
    public function __construct(
        #[MapInputName('song_title')]
        public string $title,
    ) {
    }
}
```

The validation rules for this class will be:

```php
[
    'song_title' => ['required', 'string'],
]
```

There's one small catch when the validation fails; the error message will be for the original property name, not the mapped property name. This is a small quirk we hope to solve as soon as possible. 

## Retrieving validation rules for a data object

You can retrieve the validation rules a data object will generate as such:

```php
AlbumData::getValidationRules($payload);
```

This will produce the following array with rules:

```php
[
    'title' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'songs.*.title' => ['required', 'string'],
    'songs.*.artist' => ['required', 'string'],
]
```

### Payload requirement

We suggest always providing a payload when generating validation rules. Because such a payload is used to determine which rules will be generated and which can be skipped.
