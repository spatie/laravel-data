---
title: Filling properties from route parameters
weight: 9
---

When creating data objects from requests, it's possible to automatically fill data properties from request route parameters, such as route models.

## Filling properties from a route parameter

The `FromRouteParameter` attribute allows filling properties with route parameter values. 

### Using simple (scalar) route parameters

```php
Route::patch('/songs/{songId}', [SongController::class, 'update']);

class SongData extends Data {
    #[FromRouteParameter('songId')]
    public int $id;
    public string $name;
}
```
Here, the `$id` property will be filled with the `songId` route parameter value (which most likely is a string or integer).
### Using Models, objects or arrays as route parameters**


Given that we have a route to create songs for a specific author, and that the `{author}` route parameter uses route model binding to automatically bind to an `Author` model: 

```php
Route::post('/songs/{author}', [SongController::class, 'store']);

class SongData extends Data {
    public int $id;
    #[FromRouteParameter('author')]
    public AuthorData $author;
}
```
Here, the `$author` property will be filled with the `author` route parameter value, which will be an instance of the `Author` model. Note that Laravel Data will automatically cast the model to `AuthorData`.

## Filling properties from route parameter properties

The `FromRouteParameterProperty` attribute allows filling properties with values from route parameter properties. The main difference from `FromRouteParameter` is that the former uses the full route parameter value, while `FromRouteParameterProperty` uses a single property from the route parameter.  

In the example below we're using route model binding. `{song}` represents an instance of the `Song` model. `FromRouteParameterProperty` automatically attempts to fill the `SongData` `$id` property from `$song->id`.

```php
Route::patch('/songs/{song}', [SongController::class, 'update']);

class SongData extends Data {
    #[FromRouteParameterProperty('song')]
    public int $id;
    public string $name;
}
```
### Using custom property mapping

In the example below, `$name` property will be filled with `$song->title` (instead of `$song->name).

```php
Route::patch('/songs/{song}', [SongController::class, 'update']);

class SongData extends Data {
    #[FromRouteParameterProperty('song')]
    public int $id;
    #[FromRouteParameterProperty('song', 'title')]
    public string $name;
}
```

### Nested property mapping

Nested properties ar supported as well. Here, we fill `$singerName` from `$artist->leadSinger->name`: 

```php
Route::patch('/artists/{artist}/songs/{song}', [SongController::class, 'update']);

class SongData extends Data {
    #[FromRouteParameterProperty('song')]
    public int $id;
    #[FromRouteParameterProperty('artist', 'leadSinger.name')]
    public string $singerName;
}
```

### Other supported route parameter types

`FromRouteParameterProperty` is compatible with anything that Laravel's [`data_get()` helper](https://laravel.com/docs/9.x/helpers#method-data-get) supports. This includes most objects and arrays:

```php
// $song = ['foo' => ['bar' => ['baz' => ['qux' => 'Foonderbar!'] ] ] ];

class SongData extends Data {
    #[FromRouteParameterProperty('song', 'bar.baz.qux')]
    public int $title;
}
```

## Route parameters take priority over request body

By default, route parameters take priority over values in the request body. For example, when the song ID is present in the route model as well as request body, the ID from route model is used. 

```php
Route::patch('/songs/{song}', [SongController::class, 'update']);

// PATCH /songs/123
// { "id": 321, "name": "Never gonna give you up" }

class SongData extends Data {
    #[FromRouteParameterProperty('song')]
    public int $id;
    public string $name;
}
```
Here, `$id` will be `123` even though the request body has `321` as the ID value.

In most cases, this is useful - especially when you need the ID for a validation rule. However, there may be cases when the exact opposite is required.

The above behavior can be turned off by switching the `replaceWhenPresentInBody` flag off. This can be useful when you _intend_ to allow updating a property that is present in a route parameter, such as a slug:

```php
Route::patch('/songs/{slug}', [SongController::class, 'update']);

// PATCH /songs/never
// { "slug": "never-gonna-give-you-up", "name": "Never gonna give you up" }

class SongData extends Data {
    #[FromRouteParamete('slug', replaceWhenPresentInBody: false )]
    public string $slug;
}
```

Here, `$slug` will be `never-gonna-give-you-up` even though the route parameter value is `never`.

## Using in combination with validation rules

Filling properties from route parameters can be incredibly useful when dealing with validation rules. Some validation rules may depend on a model property that may not be present in the request body.

### Example using the Unique validation rule

Using [Laravel's unique](https://laravel.com/docs/9.x/validation#rule-unique) validation rule, it may be necessary to have the rule ignore a given ID - this is usually the ID of the model being updated

**The Data class**

```php
use \Spatie\LaravelData\Attributes\FromRouteParameterProperty;

class SongData extends Data
{
    public function __construct(
        #[FromRouteParameterProperty('song')]
        public ?int $id,
        public ?string $external_id,
        public ?string $title,
        public ?string $artist,
    ) {
    }

    public static function rules(array $payload) : array
    {   
        return [
            // Here, $payload['id'] is already filled from the route model, so the following
            // unique rule works as expected - it ignores the current model when validating
            // uniqueness of external_id 
            'external_id' => [Rule::unique('songs')->ignore(Arr::get($payload, 'id'))],
        ];
    }
}
```
**Route & Controller**

```php
Route::patch('/songs/{song}', [SongController, 'update']);

// PATCH /songs/123
// { "external_id": "remote_id_321", "name": "Never gonna give you up" }

class SongController extends Controller {

    public function update(Song $song, SongData $data)
    {
        // here, $data is already validated
    }
}
```
