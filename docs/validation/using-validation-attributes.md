---
title: Using validation attributes
weight: 3
---

It is possible to add extra rules as attributes to properties of a data object:

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

These rules will be merged together with the rules that are inferred from the data object.

So it is not required to add the `required` and `string` rule, these will be added automatically. The rules for the above data object will look like this:

```php
[
    'uuid' => ['required', 'string', 'uuid'],
    'ip' => ['required', 'string', 'max:15', 'ip', 'starts_with:192.'],
]
```

For each Laravel validation rule we've got a matching validation attribute, you can find a list of them [here](/docs/laravel-data/v4/advanced-usage/using-attributes).

## Referencing route parameters

Sometimes you need a value within your validation attribute which is a route parameter. 
Like the example below where the id should be unique ignoring the current id:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Unique('songs', ignore: new RouteParameterReference('song'))]
        public int $id,
    ) {
    }
}
```

If the parameter is a model and another property should be used, then you can do the following:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Unique('songs', ignore: new RouteParameterReference('song', 'uuid'))]
        public string $uuid,
    ) {
    }
}
```

## Referencing other fields

It is possible to reference other fields in validation attributes:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[RequiredIf('title', 'Never Gonna Give You Up')]
        public string $artist,
    ) {
    }
}
```

These references are always relative to the current data object. So when being nested like this:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $album_name,
        public SongData $song,
    ) {
    }
}
```

The generated rules will look like this:

```php
[
    'album_name' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'song.title' => ['required', 'string'],
    'song.artist' => ['string', 'required_if:song.title,"Never Gonna Give You Up"'],
]
```

If you want to reference fields starting from the root data object you can do the following:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[RequiredIf(new FieldReference('album_name', fromRoot: true), 'Whenever You Need Somebody')]
        public string $artist,
    ) {
    }
}
```

The rules will now look like this:

```php
[
    'album_name' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'song.title' => ['required', 'string'],
    'song.artist' => ['string', 'required_if:album_name,"Whenever You Need Somebody"'],
]
```

## Rule attribute

One special attribute is the `Rule` attribute. With it, you can write rules just like you would when creating a custom
Laravel request:

```php
// using an array
#[Rule(['required', 'string'])] 
public string $property

// using a string
#[Rule('required|string')]
public string $property

// using multiple arguments
#[Rule('required', 'string')]
public string $property
```

## Creating your validation attribute

It is possible to create your own validation attribute by extending the `CustomValidationAttribute` class, this class has a `getRules` method that returns the rules that should be applied to the property.

```php
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class CustomRule extends CustomValidationAttribute
{
    /**
     * @return array<object|string>|object|string
     */
    public function getRules(ValidationPath $path): array|object|string
    {
        return [new CustomRule()];
    }
}
```

Quick note: you can only use these rules as an attribute, not as a class rule within the static `rules` method of the data class.
