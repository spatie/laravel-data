---
title: Creating a data object
weight: 1
---

Let's get started with the following simple data object:

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

Since this is just a simple PHP object it can be initialized as such:

```php
new SongData('Never gonna give you up', 'Rick Astley');
```

But with this package you can initialize the data object also with an array:

```php
SongData::from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

Actually you can use the `from` method to create a data object from nearly anything, for example, let's say you have an Eloquent model like this:

```php
class Song extends Model{

}
```

You can create a data object from such a model like this:

```php
SongData::from(Song::firstOrFail($id));
```

The package will try to find the required properties within the model and uses them to construct the data object.

## Magical creation

It is possible to overwrite the behaviour of the `from` method for specific types. So a data object will be constructed in a specific manner for that type. You can do this by adding the following method to the data object:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function fromModel(Song $song): self
    {
        return new self("{$song->title} ({$song->year})", $song->artist);
    }
}
```

Now when creating a data object from a model like this:

```php
SongData::from(Song::firstOrFail($id));
```

The `fromModel` method will be called instead of the default method to create a data object from model.

You're truly free to add as many from methods as you want, for example, you could add one to create a data object from string:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function fromString(string $string): self
    {
        [$title, $artist] = explode('|', $string);
    
        return new self($title, $artist);
    }
}
```

From now on you can create a data object like this:

```php
SongData::from('Never gonna give you up|Rick Astley');
```

There are a few requirements to enable magical data object creation:

- The method must be **static and public**
- The method must **start with from**
- The method can only take **one typed parameter** for which you want to create an object
- The method cannot be called **from**

When no such method can be found for a type given to the `from` the data object will try to create itself from the following types:

- An *Eloquent model* by calling `toArray` on it
- A *Laravel request* by calling `all` on it
- An *Arrayable* by calling `toArray` on it
- An *array*

When a data object cannot be created using magical methods or the default methods, a `CannotCreateDataFromValue` will be thrown.

## Optional creation

It is not possible to return `null` from the `from` method of a data object, since we always expect a data object when calling `from`. To solve this you can call the `optional` method:

```php
SongData::optional(null); // returns null
```

Underneath the optional method will call the `from` method when a value is given, so you can still magically create data objects.
