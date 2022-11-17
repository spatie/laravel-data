---
title: Nesting 
weight: 3
---

It is possible to nest multiple data objects:

```php
class ArtistData extends Data
{
    public function __construct(
        public string $name,
        public int $age,
    ) {
    }
}

class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public ArtistData $artist,
    ) {
    }
}
```

You can now create a data object as such:

```php
new AlbumData(
    'Never gonna give you up',
    new ArtistData('Rick Astley', 22)
);
```

Or you could create it from an array using a magic creation method:

```php
AlbumData::from([
    'title' => 'Never gonna give you up',
    'artist' => [
        'name' => 'Rick Astley',
        'age' => 22
    ]
]);
```

