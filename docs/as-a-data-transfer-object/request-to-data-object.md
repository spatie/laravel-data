---
title: From a request
weight: 10
---

You can create a data object by the values given in the request.

For example, let's say you send a POST request to an endpoint with the following data:

```json
{
    "title" : "Never gonna give you up",
    "artist" : "Rick Astley"
}
```

This package can automatically resolve a `SongData` object from these values by using the `SongData` class we saw in an
earlier chapter:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

You can now inject the `SongData` class in your controller. It will already be filled with the values found in the
request.

```php
class UpdateSongController
{
    public function __invoke(
        Song $model,
        SongData $data
    ){
        $model->update($data->all());
        
        return redirect()->back();
    }
}
```

As an added benefit, these values will be validated before the data object is created. If the validation fails, a `ValidationException` will be thrown which will look like you've written the validation rules yourself.

The package will also automatically validate all requests when passed to the from method:

```php
class UpdateSongController
{
    public function __invoke(
        Song $model,
        SongRequest $request
    ){
        $model->update(SongData::from($request)->all());
        
        return redirect()->back();
    }
}
```

We have a complete section within these docs dedicated to validation, you can find it [here](/docs/laravel-data/v4/validation/introduction).

## Getting the data object filled with request data from anywhere

You can resolve a data object from the container.

```php
app(SongData::class);
```

We resolve a data object from the container, its properties will already be filled by the values of the request with matching key names.
If the request contains data that is not compatible with the data object, a validation exception will be thrown.


## Validating a collection of data objects:

Let's say we want to create a data object like this from a request:

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

Since the `SongData` has its own validation rules, the package will automatically apply them when resolving validation
rules for this object.

In this case the validation rules for `AlbumData` would look like this:

```php
[
    'title' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'songs.*.title' => ['required', 'string'],
    'songs.*.artist' => ['required', 'string'],
]
```
