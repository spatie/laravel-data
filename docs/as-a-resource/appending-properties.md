---
title: Appending properties
weight: 4
---

It is possible to add some extra properties to your data objects when they are transformed into a resource:

```php
SongData::from(Song::first())->additional([
    'year' => 1987,
]);
```

This will output the following array:

```php
[
    'name' => 'Never gonna give you up',
    'artist' => 'Rick Astley',
    'year' => 1987,
]
```

When using a closure, you have access to the underlying data object:

```php
SongData::from(Song::first())->additional([
    'slug' => fn(SongData $songData) => Str::slug($songData->title),
]);
```

Which produces the following array:

```php
[
    'name' => 'Never gonna give you up',
    'artist' => 'Rick Astley',
    'slug' => 'never-gonna-give-you-up',
]
```

It is also possible to add extra properties by overwriting the `with` method within your data object:

```php
class SongData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $artist
    ) {
    }

    public static function fromModel(Song $song): self
    {
        return new self(
            $song->id,
            $song->title,
            $song->artist
        );
    }
    
    public function with()
    {
        return [
            'endpoints' => [
                'show' => action([SongsController::class, 'show'], $this->id),
                'edit' => action([SongsController::class, 'edit'], $this->id),
                'delete' => action([SongsController::class, 'delete'], $this->id),
            ]
        ];
    }
}
```

Now each transformed data object contains an `endpoints` key with all the endpoints for that data object:

```php
[
    'id' => 1,
    'name' => 'Never gonna give you up',
    'artist' => 'Rick Astley',
    'endpoints' => [
        'show' => 'https://spatie.be/songs/1',
        'edit' => 'https://spatie.be/songs/1',
        'delete' => 'https://spatie.be/songs/1',
    ],
]
```
