---
title: Collections
weight: 4
---

The package provides next to the `Data` class also a `DataCollection`, `PaginatedCollection` and `CursorPaginatedCollection` class. This collection can store a set of data objects, and we advise you to use it when storing a collection of data objects within a data object.

For example:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        #[DataCollectionOf(SongData::class)]
        public DataCollection $songs,
    ) {
    }
}
```

Using specific data collections is required for internal state management within the data object, which will become clear in the following chapters.

## Creating `DataCollection`s

There are a few different ways to create a `DataCollection`:

```php
SongData::collection([
    ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
    ['title' => 'Giving Up on Love', 'artist' => 'Rick Astley'],
]);
```

If you have a collection of models, you can do the following:

```php
SongData::collection(Song::all());
```

It is even possible to add a collection of data objects:

```php
SongData::collection([
    SongData::from(['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley']),
    SongData::from(['title' => 'Giving Up on Love', 'artist' => 'Rick Astley']),
]);
```

A `DataCollection` just works like a regular array:

```php
$collection = SongData::collection([
    SongData::from(['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'])
]);

// Count the amount of items in the collection
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

## Paginated collections

It is also possible to pass in a paginated collection:

```php
SongData::collection(Song::paginate());
```

This will return a `PaginatedDataCollection` instead of a `DataCollection`.

A cursor paginated collection can also be used:

```php
SongData::collection(Song::cursorPaginate());
```

This will result into a `CursorPaginatedCollection`

## Typing data within your collections

When nesting a data collection into your data object, always type the kind of data objects that will be stored within the collection:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        #[DataCollectionOf(SongData::class)]
        public DataCollection $songs,
    ) {
    }
}
```

Because we typed `$songs` as `SongData`, the package automatically knows it should create `SongData` objects when creating an `AlbumData` object from an array.

There are quite a few ways to type data collections:

```php
// Without namespace

/** @var SongData[] */
public DataCollection $songs;

// With namespace

/** @var \App\Data\SongData[] */
public DataCollection $songs;

// As an array

/** @var array<SongData> */
public DataCollection $songs;

// As a data collection

/** @var \Spatie\LaravelData\DataCollection<SongData> */
public DataCollection $songs;

// With an attribute

#[DataCollectionOf(SongData::class)]
public DataCollection $songs;
```

You're free to use one of these annotations/attributes as long as you're using one of them when adding a data collection to a data object.

## `DataCollection` methods

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

You can for example get the first item within a collection like this:

```php
SongData::collection(Song::all())->first(); // SongData object
```
