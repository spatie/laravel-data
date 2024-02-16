---
title: From data to array
weight: 1
---

A data object can automatically be transformed into an array as such:

```php
SongData::from(Song::first())->toArray();
```

Which will output the following array:

```php
[
    'name' => 'Never gonna give you up',
    'artist' => 'Rick Astley'
]
```

By default, calling `toArray` on a data object will recursively transform all properties to an array. This means that nested data objects and collections of data objects will also be transformed to arrays. Other complex types like `Carbon`, `DateTime`, `Enums`, etc... will be transformed into a string. We'll see in the [transformers](/docs/laravel-data/v4/as-a-resource/transformers) section how to configure and customize this behavior.

If you only want to transform a data object to an array without transforming the properties, you can call the `all` method:

```php
SongData::from(Song::first())->all();
```

You can also manually transform a data object to JSON:

```php
SongData::from(Song::first())->toJson();
```

## Using collections

Here's how to create a collection of data objects:

```php
SongData::collect(Song::all());
```

A collection can be transformed to array:

```php
SongData::collect(Song::all())->toArray();
```

Which will output the following array:

```php
[
    [
        "name": "Never Gonna Give You Up",
        "artist": "Rick Astley"
    ],
    [
        "name": "Giving Up on Love",
        "artist": "Rick Astley"
    ] 
]
```

## Nesting

It is possible to nest data objects.

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        public string $email,
        public SongData $favorite_song,
    ) {
    }
    
    public static function fromModel(User $user): self
    {
        return new self(
            $user->title,
            $user->email,
            SongData::from($user->favorite_song)
        );
    }
}
```

When transformed to an array, this will look like the following:

```php
[
    "name": "Ruben",
    "email": "ruben@spatie.be",
    "favorite_song": [
        "name" : "Never Gonna Give You Up",
        "artist" : "Rick Astley"
    ]
]
```

You can also nest a collection of data objects:

```php
class AlbumData extends Data
{
    /**
    * @param Collection<int, SongData> $songs
    */
    public function __construct(
        public string $title,
        public array $songs,
    ) {
    }

    public static function fromModel(Album $album): self
    {
        return new self(
            $album->title,
            SongData::collect($album->songs)
        );
    }
}
```

As always, remember to type collections of data objects by annotation or the `DataCollectionOf` attribute, this is essential to transform these collections correctly.
