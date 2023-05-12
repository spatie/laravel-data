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

## Using defaults for null database values

By default, if a database value is `null`, then the model attribute will also be `null`. However, sometimes you might want to instantiate the attribute with some default values.

To achieve this, you may provide an additional `default` [Cast Parameter](https://laravel.com/docs/eloquent-mutators#cast-parameters) to ensure the caster gets instantiated.

```php
class Song extends Model
{
    protected $casts = [
        'artist' => ArtistData::class . ':default',
    ];
}
```

This will ensure that the `ArtistData` caster is instantiated even when the `artist` attribute in the database is `null`.

You may then specify some default values in the cast which will be used instead.

```php
class ArtistData extends Data 
{
    public string $name = 'Default name';
}
```

```php
Song::findOrFail($id)->artist->name; // 'Default name'
```

### Nullable collections

You can also use the `default` argument in the case where you _always_ want a `DataCollection` to be returned.

The first argument (after `:`) should always be the data class to be used with the `DataCollection`, but you can add `default` as a comma separated second argument.

```php
class Artist extends Model
{
    protected $casts = [
        'songs' => DataCollection::class.':'.SongData::class.',default',
    ];
}
```

```php
$artist = Artist::create([
    'songs' => null
]);

$artist->songs; // DataCollection
$artist->songs->count();// 0
```
