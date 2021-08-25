---
title: Collections 
weight: 2
---

Next to a `Data` class , the package also provides a `DataCollection` class that can be used to store a collection of
data objects. We advise you when storing a collection of data objects within a data object to store them in
a `DataCollection` like this:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        /** @var SongData[] */
        public DataCollection $songs,
    ) {
    }
}
```

The reason for this will become clear in the next chapters because the package requires this for internal state
management.

## Creating `DataCollections`

There are a few different ways to create a `DataCollection`:

```php
SongData::collection([
    ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
    ['title' => 'Giving Up on Love', 'artist' => 'Rick Astley'],
]);
```

If you have a collection of models you can do the following:

```php
SongData::collection(Song::all());
```

It is also possible to pass in a paginated collection:

```php
SongData::collection(Song::paginate());
```

It is even possible to add a collection of data objects:

```php
SongData::collection([
    SongData::from(['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley']),
    SongData::from(['title' => 'Giving Up on Love', 'artist' => 'Rick Astley']),
]);
```

A `DataCollection` can just work like a regular array:

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

