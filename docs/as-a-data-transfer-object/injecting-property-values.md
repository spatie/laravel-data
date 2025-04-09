---
title: Injecting property values
weight: 12
---

When creating a data object, it is possible to inject values into properties from all kinds of sources like route
parameters, the current user or dependencies in the container.

## Filling properties from a route parameter

When creating data objects from requests, it's possible to automatically fill data properties from request route
parameters, such as route models.

The `FromRouteParameter` attribute allows filling properties with route parameter values.

### Using scalar route parameters

```php
Route::patch('/songs/{songId}', [SongController::class, 'update']);

class SongData extends Data {
    #[FromRouteParameter('songId')]
    public int $id;
    public string $name;
}
```

Here, the `$id` property will be filled with the `songId` route parameter value (which most likely is a string or
integer).

### Using Models, objects or arrays as route parameters

Given that we have a route to create songs for a specific author, and that the `{author}` route parameter uses route
model binding to automatically bind to an `Author` model:

```php
Route::post('/songs/{artist}', [SongController::class, 'store']);

class SongData extends Data {
    public int $id;
    #[FromRouteParameter('artist')]
    public ArtistData $author;
}
```

Here, the `$artist` property will be filled with the `artist` route parameter value, which will be an instance of the
`Artist` model. Note that the package will automatically cast the model to `ArtistData`.

## Filling properties from route parameter properties

The `FromRouteParameterProperty` attribute allows filling properties with values from route parameter properties. The
main difference from `FromRouteParameter` is that the former uses the full route parameter value, while
`FromRouteParameterProperty` uses a single property from the route parameter.

In the example below, we're using route model binding. `{song}` represents an instance of the `Song` model.
`FromRouteParameterProperty` automatically attempts to fill the `SongData` `$id` property from `$song->id`.

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

Nested properties are supported as well. Here, we fill `$singerName` from `$artist->leadSinger->name`:

```php
Route::patch('/artists/{artist}/songs/{song}', [SongController::class, 'update']);

class SongData extends Data {
    #[FromRouteParameterProperty('song')]
    public int $id;
    #[FromRouteParameterProperty('artist', 'leadSinger.name')]
    public string $singerName;
}
```

## Route parameters take priority over request body

By default, route parameters take priority over values in the request body. For example, when the song ID is present in
the route model as well as request body, the ID from route model is used.

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

In most cases, this is useful - especially when you need the ID for a validation rule. However, there may be cases when
the exact opposite is required.

The above behavior can be turned off by switching the `replaceWhenPresentInPayload` flag off. This can be useful when
you _intend_ to allow updating a property that is present in a route parameter, such as a slug:

```php
Route::patch('/songs/{slug}', [SongController::class, 'update']);

// PATCH /songs/never
// { "slug": "never-gonna-give-you-up", "name": "Never gonna give you up" }

class SongData extends Data {
    #[FromRouteParameter('slug', replaceWhenPresentInPayload: false )]
    public string $slug;
}
```

Here, `$slug` will be `never-gonna-give-you-up` even though the route parameter value is `never`.

## Filling properties from the authenticated user

The `FromCurrentUser` attribute allows filling properties with values from the authenticated user.

```php
class SongData extends Data {
    #[FromAuthenticatedUser]
    public UserData $user;
}
```

It is possible to specify the guard to use when fetching the user:

```php
class SongData extends Data {
    #[FromAuthenticatedUser('api')]
    public UserData $user;
}
```

Just like with route parameters, it is possible to fill properties with specific user properties using
`FromAuthenticatedUserProperty`:

```php
class SongData extends Data {
    #[FromAuthenticatedUserProperty('api','name')]
    public string $username;
}
```

All the other features like custom property mapping and not replacing values when present in the payload are supported
as well.

## Filling properties from the container

The `FromContainer` attribute allows filling properties with dependencies from the container.

```php
class SongData extends Data {
    #[FromContainer(SongService::class)]
    public SongService $song_service;
}
```

When a dependency requires additional parameters these can be provided as such:

```php
class SongData extends Data {
    #[FromContainer(SongService::class, parameters: ['year' => 1984])]
    public SongService $song_service;
}
```

It is even possible to completely inject the container itself:

```php
class SongData extends Data {
    #[FromContainer]
    public Container $container;
}
```

Selecting a property from a dependency can be done using `FromContainerProperty`:

```php
class SongData extends Data {
    #[FromContainerProperty(SongService::class, 'name')]
    public string $service_name;
}
```

Again, all the other features like custom property mapping and not replacing values when present in the payload are
supported as well.

## Creating your own injectable attributes

All the attributes we saw earlier implement the `InjectsPropertyValue` interface:

```php
interface InjectsPropertyValue
{
    public function resolve(
        DataProperty $dataProperty,
        mixed $payload,
        array $properties,
        CreationContext $creationContext
    ): mixed;

    public function shouldBeReplacedWhenPresentInPayload() : bool;
}
```

It is possible to create your own attribute by implementing this interface. The `resolve` method is responsible for
returning the value that should be injected into the property. The `shouldBeReplacedWhenPresentInPayload` method should
return `true` if the value should be replaced when present in the payload.
