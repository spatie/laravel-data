---
title: From data to resource
weight: 2
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

### Collections

Returning a data collection from the controller like this:

```php
SongData::collect(Song::all());
```

Will return a collection automatically transformed to JSON:

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

### Paginators

It is also possible to provide a paginator:

```php
SongData::collect(Song::paginate());
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

Or filter the properties that should be included in the empty response:

```php
SongData::empty(only: ['name']); // Will only return the `name` property

SongData::empty(except: ['name']); // Will return the `artist` property
```

## Response status code

When a resource is being returned from a controller, the status code of the response will automatically be set to `201 CREATED` when Laravel data detects that the request's method is `POST`.  In all other cases, `200 OK` will be returned.

## Resource classes

To make it a bit more clear that a data object is a resource, you can use the `Resource` class instead of the `Data` class:

```php
use Spatie\LaravelData\Resource;

class SongResource extends Resource
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

These resource classes have as an advantage that they won't validate data or check authorization, They are only used to transform data which makes them a bit faster.
