---
title: Collections
weight: 3
---

It is possible to create a collection of data objects by using the `collect` method:

```php
SongData::collect([
    ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
    ['title' => 'Giving Up on Love', 'artist' => 'Rick Astley'],
]); // returns an array of SongData objects
```

Whatever type of collection you pass in, the package will return the same type of collection with the freshly created
data objects within it. As long as this type is an array, Laravel collection or paginator or a class extending from it.

This opens up possibilities to create collections of Eloquent models:

```php
SongData::collect(Song::all()); // return an Eloquent collection of SongData objects
```

Or use a paginator:

```php
SongData::collect(Song::paginate()); // return a LengthAwarePaginator of SongData objects

// or

SongData::collect(Song::cursorPaginate()); // return a CursorPaginator of SongData objects
```

Internally the `from` method of the data class will be used to create a new data object for each item in the collection.

When the collection already contains data objects, the `collect` method will return the same collection:

```php
SongData::collect([
    SongData::from(['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley']),
    SongData::from(['title' => 'Giving Up on Love', 'artist' => 'Rick Astley']),
]); // returns an array of SongData objects
```

The collect method also allows you to cast collections from one type into another. For example, you can pass in
an `array`and get back a Laravel collection:

```php
SongData::collect($songs, Collection::class); // returns a Laravel collection of SongData objects
```

This transformation will only work with non-paginator collections.

## Magically creating collections

We've already seen that `from` can create data objects magically. It is also possible to create a collection of data
objects magically when using `collect`.

Let's say you've implemented a custom collection class called `SongCollection`:

```php
class SongCollection extends Collection
{
    public function __construct(
        $items = [],
        public array $artists = [],
    ) {
        parent::__construct($items);
    }
}
```

Since the constructor of this collection requires an extra property it cannot be created automatically. However, it is
possible to define a custom collect method which can create it:

```php
class SongData extends Data
{
    public string $title;
    public string $artist;

    public static function collectArray(array $items): SongCollection
    {
        return new SongCollection(
            parent::collect($items),
            array_unique(array_map(fn(SongData $song) => $song->artist, $items))
        );
    }
}
```

Now when collecting an array data objects a `SongCollection` will be returned:

```php
SongData::collectArray([
    ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
    ['title' => 'Living on a prayer', 'artist' => 'Bon Jovi'],
]); // returns an SongCollection of SongData objects
```

There are a few requirements for this to work:

- The method must be **static**
- The method must be **public**
- The method must have a **return type**
- The method name must **start with collect**
- The method name must not be **collect**

## Creating a data object with collections

You can create a data object with a collection of data objects just like you would create a data object with a nested
data object:

```php
use App\Data\SongData;
use Illuminate\Support\Collection;

class AlbumData extends Data
{    
    public string $title;
    /** @var Collection<int, SongData> */
    public Collection $songs;
}

AlbumData::from([
    'title' => 'Never Gonna Give You Up',
    'songs' => [
        ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
        ['title' => 'Giving Up on Love', 'artist' => 'Rick Astley'],
    ]
]);
```

Since the collection type here is a `Collection`, the package will automatically convert the array into a collection of
data objects.

## DataCollections, PaginatedDataCollections and CursorPaginatedCollections

The package also provides a few collection classes which can be used to create collections of data objects. It was a
requirement to use these classes in the past versions of the package when nesting data objects collections in data
objects. This is no longer the case, but there are still valid use cases for them.

You can create a DataCollection like this:

```php
use Spatie\LaravelData\DataCollection;

SongData::collect(Song::all(), DataCollection::class);
```

A PaginatedDataCollection can be created like this:

```php
use Spatie\LaravelData\PaginatedDataCollection;

SongData::collect(Song::paginate(), PaginatedDataCollection::class);
````

And a CursorPaginatedCollection can be created like this:

```php
use Spatie\LaravelData\CursorPaginatedCollection;

SongData::collect(Song::cursorPaginate(), CursorPaginatedCollection::class);
```

### Why using these collection classes?

We advise you to always use arrays, Laravel collections and paginators within your data objects. But let's say you have
a controller like this:

```php
class SongController
{
    public function index()
    {
        return SongData::collect(Song::all());    
    }
}
```

In the next chapters of this documentation, we'll see that it is possible to include or exclude properties from the data
objects like this:

```php
class SongController
{
    public function index()
    {
        return SongData::collect(Song::all(), DataCollection::class)->include('artist');    
    }
}
```

This will only work when you're using a `DataCollection`, `PaginatedDataCollection` or `CursorPaginatedCollection`.

### DataCollections

DataCollections provide some extra functionalities like:

```php
// Counting the amount of items in the collection
count($collection);

// Changing an item in the collection
$collection[0]->title = 'Giving Up on Love';

// Adding an item to the collection
$collection[] = SongData::from(['title' => 'Never Knew Love', 'artist' => 'Rick Astley']);

// Removing an item from the collection
unset($collection[0]);
```

It is even possible to loop over it with a foreach:

```php
foreach ($songs as $song){
    echo $song->title;
}
```

The `DataCollection` class implements a few of the Laravel collection methods:

- through
- map
- filter
- first
- each
- values
- where
- reduce
- sole

You can, for example, get the first item within a collection like this:

```php
SongData::collect(Song::all(), DataCollection::class)->first(); // SongData object
```

### The `collection` method

In previous versions of the package it was possible to use the `collection` method to create a collection of data
objects:

```php
SongData::collection(Song::all()); // returns a DataCollection of SongData objects
SongData::collection(Song::paginate()); // returns a PaginatedDataCollection of SongData objects
SongData::collection(Song::cursorPaginate()); // returns a CursorPaginatedCollection of SongData objects
```

This method was removed with version v4 of the package in favor for the more powerful `collect` method. The `collection`
method can still be used by using the `WithDeprecatedCollectionMethod` trait:

```php
use Spatie\LaravelData\Concerns\WithDeprecatedCollectionMethod;

class SongData extends Data
{
    use WithDeprecatedCollectionMethod;
    
    // ...
}
```

Please note that this trait will be removed in the next major version of the package.
