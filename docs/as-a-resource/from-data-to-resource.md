---
title: From data to resource
weight: 1
---

A data object will automatically be transformed to a JSON response when returned in a controller:

```php
class SongController
{
    public function show(Song $model)
    {
        return SongData::from($model);
    }
}
```

The JSON then will look like this:

```json
{
    "name": "Never gonna give you up",
    "artist": "Rick Astley"
}
```

You can manually transform a data object to JSON:

```php
SongData::from(Song::first())->toJson();
```

Or transform a data object to an array:

```php
SongData::from(Song::first())->toArray();
```

## Transforming empty objects

When creating a new model, you probably want to provide a blueprint to the frontend with the required data to create a model. For example:

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
        public ?string $title,
        public ?string $artist,
    ) {
    }

    // ...
}
```

This approach would work, but as soon as the model is created, the properties won't be `null`, which doesn't follow our data model. So it is considered a bad practice.

That's why in such cases, you can return an empty representation of the data object:

```php
class SongsController
{
    public function create(): array
    {
        return SongData::empty();
    }
}
```

Which will output the following JSON:

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
        public string $title = 'Title of the song here',
        public string $artist = "An artist",
    ) {
    }
    
    // ...
}
 ```

Now when we call `empty`, our JSON looks like this:

```json
{
    "name": "Title of the song here",
    "artist": "An artist"
}
``` 

You can also pass defaults within the `empty` call:

```php
SongData::empty([
    'name' => 'Title of the song here',
    'artist' => 'An artist'
]);
```

## Mapping property names

Sometimes you might want to change the name of a property, with attributes this is possible:

```php
class ContractData extends Data
{
    public function __construct(
        public string $name,
        #[MapOutputName('record_company')]
        public string $recordCompany,
    ) {
    }
}
```

Now our JSON looks like this:

```json
{
    "name": "Rick Astley",
    "record_company": "RCA Records"
}
``` 

Changing all property names in a data object to snake_case as output data can be done as such:

```php
#[MapOutputName(SnakeCaseMapper::class)]
class ContractData extends Data
{
    public function __construct(
        public string $name,
        public string $recordCompany,
    ) {
    }
}
```

You can also use the `MapName` attribute when you want to combine input and output property name mapping:

```php
#[MapName(SnakeCaseMapper::class)]
class ContractData extends Data
{
    public function __construct(
        public string $name,
        public string $recordCompany,
    ) {
    }
}
```

## Using collections

Here's how to create a collection of data objects:

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
    }
]
```

You can also transform a collection of data objects into an array:

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
        }
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
$allSongs = Song::all();

SongData::collection($allSongs)->through(function(SongData $song){
    $song->artist = 'Abba';
    
    return $song;
});
```

You can filter non-paginated collections:

```php
SongData::collection($allSongs)->filter(
    fn(SongData $song) => $song->artist === 'Rick Astley'
);
```

## Nesting

It is possible to nest data objects.

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        public string $email,
        public SongData $favorite_song,
    ) {
    }
    
    public static function fromModel(User $user): self
    {
        return new self(
            $user->title,
            $user->email,
            SongData::from($user->favorite_song)
        );
    }
}
```

When transformed to JSON, this will look like the following:

```json
{
    "name": "Ruben",
    "email": "ruben@spatie.be",
    "favorite_song": {
        "name" : "Never Gonna Give You Up",
        "artist" : "Rick Astley"
    }
}
```

You can also nest a collection of resources:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        #[DataCollectionOf(SongData::class)]
        public DataCollection $songs,
    ) {
    }

    public static function fromModel(Album $album): self
    {
        return new self(
            $album->title,
            SongData::collection($album->songs)
        );
    }
}
```

We're using a `DataCollection` type here in the data object definition. It would be best always to use a `DataCollection` type when nesting a collection of data objects. The package requires this for internal state management.

## Appending properties

It is possible to add some extra properties to your data objects when they are transformed into a resource:

```php
SongData::from(Song::first())->additional([
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
SongData::from(Song::first())->additional([
    'slug' => fn(SongData $songData) => Str::slug($songData->title),
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

Now each transformed data object contains an `endpoints` key with all the endpoints for that data object:

```json
{
    "id": 1,
    "name": "Never gonna give you up",
    "artist": "Rick Astley",
    "endpoints": {
        "show":  "https://spatie.be/songs/1",
        "edit":  "https://spatie.be/songs/1",
        "delete":  "https://spatie.be/songs/1"
    }
}
```
 ## Response status code

When a resource is being returned from a controller, the status code of the response will automatically be set to `201 CREATED` when Laravel data detectes that the request's method is `POST`.  In all other cases, `200 OK` will be returned.

