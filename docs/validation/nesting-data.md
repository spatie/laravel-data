---
title: Nesting Data
weight: 6
---

A data object can contain other data objects or collections of data objects. The package will make sure that also for these data objects validation rules will be generated.

Let's take a look again at the data object from the [nesting](/docs/laravel-data/v4/as-a-data-transfer-object/nesting) section:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public ArtistData $artist,
    ) {
    }
}
```

The validation rules for this class would be:

```php
[
    'title' => ['required', 'string'],
    'artist' => ['array'],
    'artist.name' => ['required', 'string'],
    'artist.age' => ['required', 'integer'],
]
```

## Validating a nested collection of data objects

When validating a data object like this

```php
class AlbumData extends Data
{
    /**
    * @param array<int, SongData> $songs
    */
    public function __construct(
        public string $title,
        public array $songs,
    ) {
    }
}
```

In this case the validation rules for `AlbumData` would look like this:

```php
[
    'title' => ['required', 'string'],
    'songs' => ['present', 'array', new NestedRules()],
]
```

The `NestedRules` class is a Laravel validation rule that will validate each item within the collection for the rules defined on the data class for that collection. 

## Nullable and Optional nested data

If we make the nested data object nullable, the validation rules will change depending on the payload provided:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public ?ArtistData $artist,
    ) {
    }
}
```

If no value for the nested object key was provided or the value is `null`, the validation rules will be:

```php
[
    'title' => ['required', 'string'],
    'artist' => ['nullable'],
]
```

If, however, a value was provided (even an empty array), the validation rules will be:

```php
[
    'title' => ['required', 'string'],
    'artist' => ['array'],
    'artist.name' => ['required', 'string'],
    'artist.age' => ['required', 'integer'],
]
```

The same happens when a property is made optional:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public ArtistData $artist,
    ) {
    }
}
```

There's a small difference compared against nullable, though. When no value was provided for the nested object key, the validation rules will be:

```php
[
    'title' => ['required', 'string'],
    'artist' => ['present', 'array', new NestedRules()],
]
```

However, when a value was provided (even an empty array or null), the validation rules will be:

```php
[
    'title' => ['required', 'string'],
    'artist' => ['array'],
    'artist.name' => ['required', 'string'],
    'artist.age' => ['required', 'integer'],
]
```

We've written a [blog post](https://flareapp.io/blog/fixing-nested-validation-in-laravel) on the reasoning behind these variable validation rules based upon payload. And they are also the reason why calling `getValidationRules` on a data object always requires a payload to be provided.
