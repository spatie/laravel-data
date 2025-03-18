---
title: Eloquent casting
weight: 1
---

Since data objects can be created from arrays and be easily transformed into arrays back again, they are excellent to be used
with [Eloquent casts](https://laravel.com/docs/eloquent-mutators#custom-casts):

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

### Abstract data objects

Sometimes you have an abstract parent data object with multiple child data objects, for example:

```php
abstract class RecordConfig extends Data
{
    public function __construct(
        public int $tracks,
    ) {}
}

class CdRecordConfig extends RecordConfig
{
    public function __construct(
        int $tracks,
        public int $bytes,
    ) {
        parent::__construct($tracks);
    }
}

class VinylRecordConfig extends RecordConfig
{
    public function __construct(
        int $tracks,
        public int $rpm,
    ) {
        parent::__construct($tracks);
    }
}
```

A model can have a JSON field which is either one of these data objects:

```php
class Record extends Model
{
    protected $casts = [
        'config' => RecordConfig::class,
    ];
}
```

You can then store either a `CdRecordConfig` or a `VinylRecord` in the `config` field:

```php
$cdRecord = Record::create([
    'config' => new CdRecordConfig(tracks: 12, bytes: 1000),
]);

$vinylRecord = Record::create([
    'config' => new VinylRecordConfig(tracks: 12, rpm: 33),
]);

$cdRecord->config; // CdRecordConfig object
$vinylRecord->config; // VinylRecordConfig object
```

When a data object class is abstract and used as an Eloquent cast, then this feature will work out of the box.

The child data object value of the model will be stored in the database as a JSON string with the class name and the data object properties:

```json
{
    "type": "\\App\\Data\\CdRecordConfig",
    "data": {
        "tracks": 12,
        "bytes": 1000
    }
}
```

When retrieving the model, the data object will be instantiated based on the `type` key in the JSON string.

#### Abstract data object with collection

You can use with collection.

```php
class Record extends Model
{
    protected $casts = [
        'configs' => DataCollection::class . ':' . RecordConfig::class,
    ];
}
```

#### Abstract data class morphs

By default, the `type` key in the JSON string will be the fully qualified class name of the child data object. This can break your application quite easily when you refactor your code. To prevent this, you can add a morph map like with [Eloquent models](https://laravel.com/docs/eloquent-relationships#polymorphic-relationships). Within your `AppServiceProvivder` you can add the following mapping:

```php
use Spatie\LaravelData\Support\DataConfig;

app(DataConfig::class)->enforceMorphMap([
    'cd_record_config' => CdRecordConfig::class,
    'vinyl_record_config' => VinylRecordConfig::class,
]);
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

## Using encryption with data objects and collections

Similar to Laravel's native encrypted casts, you can also encrypt data objects and collections.

When retrieving the model, the data object will be decrypted automatically.

```php
class Artist extends Model
{
    protected $casts = [
        'songs' => DataCollection::class.':'.SongData::class.',encrypted',
    ];
}
```
