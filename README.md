**Work in progress, do not use this unless you know what you're doing!**

# This is my package LaravelDataResource

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-data.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-data-resource)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-data/run-tests?label=tests)](https://github.com/spatie/laravel-data-resource/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-data/Check%20&%20fix%20styling?label=code%20style)](https://github.com/spatie/laravel-data-resource/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-data.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-data-resource)

This package allows you to create data objects in Laravel that can also be used as resources:

```php
class UserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public Carbon $birth_date,
    ) {
    }

    public static function create(User $user): static
    {
        return new self(
            $user->name,
            $user->email,
            $user->birth_date
        );
    }
}
```

You can now use this object as a data transfer object as such:

```php
$userData = new UserData(
    $request->input('name'),
    $request->input('email'),
    Carbon::make($request->input('birth_date')),
);
```

And use this object through your application code:

```php
User::create([
    'name' => $userData->name,
    'email' => $userData->email,
    'birth_date' => $userData->birth_date
]);
```

Or you could transform the data object to a resource as such:

```php
return UserData::create(Auth::user());
```

This will be transformed to a JSON version of the data object just like a Laravel resource:

```json
{
    "name": "Ruben Van Assche",
    "email": "ruben@spatie.be",
    "birth_date": "1994-05-15T00:00:00+00:00"
}
```

This package allows you to make collections, nest, lazy load data objects. And it allows the automatic tranformation of data objects to TypeScript definitions.

Though this package is perfect to create simple data transfer objects when communicating between backend and frontend. For more complicated case I recommend our [spatie/data-transfer-object](https://github.com/spatie/data-transfer-object) package.
## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-data-resource.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-data-resource)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-data
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Spatie\LaravelData\LaravelDataServiceProvider" --tag="laravel-data-config"
```

This is the contents of the published config file:

```php
return [
    /*
     * Transformers will take properties within your data objects and transform
     * them to types that can be JSON encoded.
     */
    'transformers' => [
        \Spatie\LaravelData\Transformers\DateTransformer::class,
        \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
    ]
];
```

## Usage

A data object extends from `Data` and looks like this:

```php
class SongData extends Data
{
    public function __construct(
        public string $name,
        public string $artist,
    ) {
    }

    public static function create(Song $song): self
    {
        return new self(
            $song->name,
            $song->artist
        );
    }
}
```

In the constructor we define the properties associated with this data object, only public properties will be included when transforming the data object to a resource.

Each data object also should have a static `create` method that will create the object based upon a model. This method will be called when the data object is created from a collection of models.

Now you can create the data object in multiple ways, one where you don't have a model yet:

```php
new SongData('Rick Astley', 'Never gonna give you up');
```

And one where you can use a model to create the data object:

```php
SongData::create(Song::first());
```

If you have a collection of models then you can create a collection of data objects as such:

```php
SongData::collection(Song::all());
```

### Transforming a data object

You can return a data object within a controller, it will automatically be converted to a JSON response:

```php
class SongsController
{
    public function show(Song $song)
    {
        return SongData::create($song);
    }
}
```

This will return:

```json
{
    "name": "Never gonna give you up",
    "artist": "Rick Astley"
}
```

You can also manually convert a data object to an array as such:

```php
SongData::create(Song::first())->toArray();
```

Or to JSON:

```php
SongData::create(Song::first())->toJson();
```

### Converting empty objects to an array

When you're creating a new model, you probably want to provide a blueprint to the frontend with the required data to create a model. For example:

```json
{
    "name": null,
    "artist": null
}
```

You could make each property of the data object nullable like this:

```php
class SongData extends Data
{
    public function __construct(
        public ?string $name,
        public ?string $artist,
    ) {
    }

    // ...
}
```

This would work but we know as soon as our model is created, the properties won't be `null` and this would not follow our data model. So it is considerd a bad practice.

That's why in shuch cases you can return an empty representation of the data object:

```php
class SongsController
{
    public function create()
    {
        return SongData::empty();
    }
}
```

This will output the following JSON:

```json
{
    "name": null,
    "artist": null
}
```

The `empty` method on a data object will return an array with default empty values for the properties in the data object.

It is possible to change the default values within this array by providing them in the constructor of the data object:

 ```php
 class SongData extends Data
{
    public function __construct(
        public string $name = 'Name of the song here',
        public string $artist = "An artist",
    ) {
    }
    
    // ...
}
 ```

Now when we call `empty` our JSON looks like this:

```json
{
    "name": "Name of the song here",
    "artist": "An artist"
}
``` 

You can also pass defaults within the `empty` call:

```php
SongData::empty([
    'name' => 'Name of the song here',
]);
```

### Collections

You can easily create a collection of data objects as such:

```php
SongData::collection(Song::all());
```

A collection can be returned in a controller and will automatically be transformed to JSON:

```json
[
    {
        "name": "Never Gonna Give You Up",
        "artist": "Rick Astley"
    },
    {
        "name": "Giving Up on Love",
        "artist": "Rick Astley"
    },
    
    ...
]
```

A collection of data objects can also be transformed to an array:

```php
SongData::collection(Song::all())->toArray();
```

It is also possible to provide a paginated collection:

```php
SongData::collection(Song::paginate());
```

The data object is smart enough to create a paginated response from this with links to the next, previous, last, ... pages:

```json
{
    "data" : [
        {
            "name" : "Never Gonna Give You Up",
            "artist" : "Rick Astley"
        },
        {
            "name" : "Giving Up on Love",
            "artist" : "Rick Astley"
        },

        ...
    ],
    "meta" : {
        "current_page": 1,
        "first_page_url": "https://spatie.be/?page=1",
        "from": 1,
        "last_page": 7,
        "last_page_url": "https://spatie.be/?page=7",
        "next_page_url": "https://spatie.be/?page=2",
        "path": "https://spatie.be/",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 100
    }
}
```

It is possible to change data objects in a collection:

```php
SongData::collection(Song::all())->transform(function(SongData $song){
    $song->artist = 'Abba';
    
    return $song;
});
```

You can also filter non-paginated collections:

```php
SongData::collection(Song::all())->filter(
    fn(SongData $song) => $song->artist === 'Rick Astley'
);
```

### Nesting

It is possible to nest data objects as such:

```php
class UserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public SongData $favorite_song,
    ) {
    }

    public static function create(User $user): self
    {
        return new self(
            $user->name,
            $user->email,
            SongData::create($user->favorite_song)
        );
    }
}
```

You can also nest a collection of resources:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $name,
        /** @var SongData[] */
        public DataCollection $songs,
    ) {
    }

    public static function create(Album $album): self
    {
        return new self(
            $album->name,
            SongData::collection($album->songs)
        );
    }
}
```

We're using a `DataCollection` here. You should always use a `DataCollection` type when nesting a collection of data objects. The package requires this for internal state management.

### Lazy properties

Sometimes you don't want all the properties included when transforming a data object to an array, for example:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $name,
        /** @var SongData[] */
        public DataCollection $songs,
    ) {
    }
}
```

This will always output a collection of songs, which can become quite large. With lazy properties we can include properties when we want to:


```php
class AlbumData extends Data
{
    public function __construct(
        public string $name,
        /** @var SongData[] */
        public Lazy|DataCollection $songs,
    ) {
    }
    
    public static function create(Album $album): self
    {
        return new self(
            $album->name,
            Lazy::create(fn() => SongData::collection($album->songs))
        );
    }
}
```

The songs won't be included in the data object when we create it from a model because the closure that provides the data won't be called when transforming the data object.

Now when we transform the data object as such:

```php
AlbumData::create(Album::first())->toArray();
```

We get the following JSON:

```json
{
    "name": "Together Forever"
}
```

As you can see the `songs` property is missing in the JSON output, it can explicitly be included as such:

```php
AlbumData::create(Album::first())->include('songs');
```

#### Including lazy properties

Lazy properties can be included in different ways:

```php
Lazy::create(fn() => SongData::collection($album->songs));
```

Will only be included when the `include` method is called on the data object with the name of the property.

It is also possible to nest these includes. For example, let's update the `SongData` class as such:

```php
class SongData extends Data
{
    public function __construct(
        public Lazy|string $name,
        public Lazy|string $artist,
    ) {
    }

    public static function create(Song $song): self
    {
        return new self(
            Lazy::create(fn() => $song->name),
            Lazy::create(fn() => $song->artist)
        );
    }
}
```

Now `name` or `artist` should be explicitly included, this can be done as such on the `AlbumData`:

```php
AlbumData::create(Album::first())->include('songs.name', 'songs.artist');
```

Or you could combine these includes:

```php
AlbumData::create(Album::first())->include('songs.{name, artist}');
```

If you want to include all the properties of a data object you can do the following:

```php
AlbumData::create(Album::first())->include('songs.*');
```

Explicitly including properties of resources also works on a single resource, for example our `UserData` looks like this:

```php
class UserData extends Data
{
    public function __construct(
        public string $name,
        public Lazy|SongData $favorite_song,
    ) {
    }

    public static function create(User $user): self
    {
        return new self(
            $user->name,
            Lazy::create(fn() => SongData::create($user->favorite_song))
        );
    }
}
```

We can include properties of the data object just like we would with collections of data objects:

```php
return UserData::create(Auth::user())->include('favorite_song.name');
```

#### Conditional Lazy properties

Sometimes you only want to include a property when a certain condition is true. This can be done with conditional lazy properties:

```php
Lazy::when($this->is_admin, fn() => SongData::collection($album->songs));
```

The property now only will be included when the `is_admin` property of the data object is true.

#### Relational Lazy properties

You can also only include a lazy property when a certain relation is loaded on the model as such:

```php
Lazy::whenLoaded('songs', fn() => SongData::collection($album->songs));
```

Now the property will only be included when the songs relation is loaded on the model.

#### Default included lazy properties

It is possible to mark a lazy property as default included:

```php
Lazy::create(fn() => SongData::collection($album->songs))->defaultIncluded();
```

The property will now always be included when the data object is transformed. You can explititly exlude properties that were default incuded as such:

```php
AlbumData::create(Album::first())->exclude('songs');
```

#### Include by query string

It is possible to include or exclude lazy properties by the url query string:

For example when we create a route `my-account`:

```php
// in web.php

Route::get('my-account', fn() => UserData::create(User::first()));
```

Our JSON would look like this when we request `https://spatie.be/my-account`:

```json
{
    "name": "Ruben Van Assche"
}
```

We can include `favorite_song` by adding it to the query in the url as such:

```
https://spatie.be/my-account?include=favorite_song
```

It is also possible to define excludes with the `exclude` key in the url query.

Including and excluding lazy properties works for data objects and data collections.

### Appending properties

It is possible to add some extra properties to your data objects when they are transformed to a resource:

```php
SongData::create(Song::first())->additional([
    'year' => 1987,
]);
```

This will output the following JSON:

```json
{
    "name": "Never gonna give you up",
    "artist": "Rick Astley",
    "year": 1987
}
```

When using a closure, you have access to the underlying data object:

```php
SongData::create(Song::first())->additional([
    'slug' => fn(SongData $songData) => Str::slug($songData->name),
]);
```

Which produces the following:

```json
{
    "name": "Never gonna give you up",
    "artist": "Rick Astley",
    "slug": "never-gonna-give-you-up"
}
```

It is also possible to add extra properties by overwriting the `with` method within your data object:

```php
class SongData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $artist
    ) {
    }

    public static function create(Song $song): self
    {
        return new self(
            $song->id,
            $song->name,
            $song->artist
        );
    }
    
    public function with(){
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

Now each converted data object contains an `endpoints` key with all the endpoints for that data object.

### Transformers

Each property of a data object should be converted into a type that is usefull to communicate via json. For example a `Carbon` object, should it be converted to `16-05-1994` or `16-05-1994T00:00:00+00`?

With this package you're free to decide how this transformation is done by using  transformers. A transformer is a simple class that implements the `Transformer` interface:

```php
class MyTransformer implements Transformer
{
    public function canTransform(mixed $value): bool
    {
        // Can this transformer transform the value?
    }

    public function transform(mixed $value): mixed
    {
        // Transform the value to the desired format
    }
}
```

You can add these transformers within the `data.php` config file. By default the package ships with two transformers:

- `DateTransformer` transforms date objects to an ISO8601 format
- `ArrayableTransformer` calls `toArray` on each `Arrayable`

### Transforming without loss of types

You can get an array representation of the data object without running transformers and keeping nested data objects and collections as they are as such:

```php
UserData::create(User::first())->all();
```

In this case the `favorite_song` within the `UserData` will still be a `SongData` object instead of an array with the transformed song data object.

It is possible to do the same on data collections:

```php
SongData::collection(Song::all())->toArray(); // Array of ['name' => '...', 'artist' => '...']

SongData::collection(Song::all())->all(); // Array of SongData
```

### Getting a TypeScript version of your data object

Thanks to the [typescript-transformer](https://github.com/spatie/typescript-transformer) package it is possible to automatically transform data objects into TypeScript definitions.

For example, the following data object:

```php
class DataObject extends Data{
    public function __construct(
        public null|int $nullable,
        public int $int,
        public bool $bool,
        public string $string,
        public float $float,
        /** @var string[] */
        public array $array,
        public Lazy|string $lazy,
        public SimpleData $simpleData,
        /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
        public DataCollection $dataCollection,
    )
    {
    }
}
```

Would be converted to the following TypeScript Type:

```tsx
{
    nullable: number | null;
    int: number;
    bool: boolean;
    string: string;
    float: number;
    array: Array<string>;
    lazy?: string;
    simpleData: SimpleData;
    dataCollection: Array<SimpleData>;
}
```

You should add the `DataTypeScriptTransformer` transformer to your transformers in the `typescript-transformer.php` config file. And annotate the data objects you want to be transformed or add the `DataTypeScriptCollector` to your collectors in `typescript-transformer.php` so they will all be transformed.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ruben Van Assche](https://github.com/rubenvanassche)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

