---
title: Eloquent casting
weight: 1
---

Since data objects can be created from array's and be easily transformed into array's back again, they are excellent to be used
with [Eloquent casts](https://laravel.com/docs/8.x/eloquent-mutators#custom-casts):

```php
class Song extends Model
{
    protected $casts = [
        'artist' => ArtistData::class,
    ];
}
```

Now you can store a data object in a model as such:

```php
Song::create([
    'artist' => new ArtistData(name: 'Rick Astley', age: 22),
]);
```

It is also possible to use an array representation of the data object:

```php
Song::create([
    'artist' => [
        'name' => 'Rick Astley',
        'age' => 22
    ]
]);
```

This will internally be converted to a data object which you can later retrieve as such:

```php
Song::findOrFail($id)->artist; // ArtistData object
```

## Casting data collections

It is also possible to store data collections in an Eloquent model:

```php
class Artist extends Model
{
    protected $casts = [
        'songs' => DataCollection::class.':'.SongData::class,
    ];
}
```

A collection of data objects within the Eloquent model can be made as such:

```php
Artist::create([
    'songs' => [
        new SongData(title: 'Never gonna give you up', artist: 'Rick Astley'),
        new SongData(title: 'Together Forever', artist: 'Rick Astley'),
    ],
]);
```

It is also possible to provide an array instead of a data object to the collection:

```php
Artist::create([
    'songs' => [
        ['title' => 'Never gonna give you up', 'artist' => 'Rick Astley'],
        ['title' => 'Together Forever', 'artist' => 'Rick Astley']
    ],
]);
```
