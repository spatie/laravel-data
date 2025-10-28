---
title: Nesting 
weight: 2
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

## Collections of data objects

What if you want to nest a collection of data objects within a data object?

That's perfectly possible, but there's a small catch; you should always define what kind of data objects will be stored
within the collection. This is really important later on to create validation rules for data objects or partially
transforming data objects.

There are a few different ways to define what kind of data objects will be stored within a collection. You could use an
annotation, for example, which has an advantage that your IDE will have better suggestions when working with the data
object. And as an extra benefit, static analyzers like PHPStan will also be able to detect errors when your code
is using the wrong types.

A collection of data objects defined by annotation looks like this:

```php
/**
 * @property \App\Data\SongData[] $songs
 */
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public array $songs,
    ) {
    }
}
```

or like this when using properties:

```php
class AlbumData extends Data
{
    public string $title;
    
    /** @var \App\Data\SongData[] */
    public array $songs;
}
```

If you've imported the data class you can use the short notation:

```php
use App\Data\SongData;

class AlbumData extends Data
{    
    /** @var SongData[] */
    public array $songs;
}
```

It is also possible to use generics:

```php
use App\Data\SongData;

class AlbumData extends Data
{    
    /** @var array<SongData> */
    public array $songs;
}
```

The same is true for Laravel collections, but be sure to use two generic parameters to describe the collection. One for the collection key type and one for the data object type.

```php
use App\Data\SongData;
use Illuminate\Support\Collection;

class AlbumData extends Data
{    
    /** @var Collection<int, SongData> */
    public Collection $songs;
}
```

If the collection is well-annotated, the `Data` class doesn't need to use annotations:

```php
/**
 * @template TKey of array-key
 * @template TData of \App\Data\SongData
 *
 * @extends \Illuminate\Support\Collection<TKey, TData>
 */
class SongDataCollection extends Collection
{
}

class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public SongDataCollection $songs,
    ) {
    }
}
```

You can also use an attribute to define the type of data objects that will be stored within a collection:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        #[DataCollectionOf(SongData::class)]
        public array $songs,
    ) {
    }
}
```

This was the old way to define the type of data objects that will be stored within a collection. It is still supported, but we recommend using the annotation since static analyzers and IDEs will have better support for that.

```php
