---
title: Request to data object weight: 2
---

Next to manually creating, creating data objects it is also possible to create a data object by the values given in the request.

For example let's say you to a POST request to an endpoint with the following data:

```json
{
    "song": "Never gonna give you up",
    "artist": "Rick Astley"
}
```

This package can automatically resolve a `SongData` object from these values by using the `SongData` class we saw iin an earlier chapter:

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

You can now resolve this object as such:

```php
app(SongData::class);
```

Or inject it in your controller as such:

```php
class SongController{
    ...
    
    public function update(
        Song $model,
        SongData $data
    ){
        $model->update($data->all());
        
        return redirect()->back();
    }
}
```

The way this works is that the package will register a callback into the Laravel container that will be called when the container wants to resolve a data object. It then will take a look at the request values and will try to make a data object out of it. There's a lot more you can do with this package, it is possible to add validation rules for requests to data object, automatically generate validation rules for data properties, authorize the creation of a data object and much more! Let's take a dive into it.

## Mapping a request onto a data object

By default, the package will do a one to one mapping from request to data object. This means that for each property within the data object, a value with the same key will be searched within the request values. 

If you want to customize this mapping, then you can always add a magical creation method like this:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function fromRequest(Request $request): static
    {
        return new self(
            {$request->input('title_of_song')}, 
            {$request->input('artist_name')}
        );
    }
}
```

### Nesting and collections

## Validating a request

When creating a data object from request, the package can also validate the values from the request that will be used to construct the data object. 

### Automatically infere rules for properties

### Overwriting the validator

### Overwriting messages

### Overwriting attributes

## Authorizing a request




