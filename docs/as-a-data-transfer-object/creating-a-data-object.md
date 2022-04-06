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

Since this is just a simple PHP object, it can be initialized as such:

```php
new SongData('Never gonna give you up', 'Rick Astley');
```

But with this package, you can initialize the data object also with an array:

```php
SongData::from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

You can use the `from` method to create a data object from nearly anything. For example, let's say you have an Eloquent
model like this:

```php
class Song extends Model{

}
```

You can create a data object from such a model like this:

```php
SongData::from(Song::firstOrFail($id));
```

The package will find the required properties within the model and use them to construct the data object.

## Magical creation

It is possible to overwrite or extend the behaviour of the `from` method for specific types. So you can construct a data
object in a specific manner for that type. This can be done by adding a static method starting with 'from' to the data
object.

For example, we want to change how we create a data object from a model. We can add a `fromModel` static method that
takes the model we want to use as a parameter:

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

Instead of the default method, the `fromModel` method will be called to create a data object from the found model.

You're truly free to add as many from methods as you want. For example, you could add one to create a data object from a
string:

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

From now on, you can create a data object like this:

```php
SongData::from('Never gonna give you up|Rick Astley');
```

There are a few requirements to enable magical data object creation:

- The method must be **static and public**
- The method must **start with from**
- The method can only take **one typed parameter** for which you want to create an object
- The method cannot be called **from**

When the package cannot find such a method for a type given to the data object's `from` method. Then the data object
will try to create itself from the following types:

- An *Eloquent model* by calling `toArray` on it
- A *Laravel request* by calling `all` on it
- An *Arrayable* by calling `toArray` on it
- An *array*

When a data object cannot be created using magical methods or the default methods, a `CannotCreateDataFromValue`
exception will be thrown.

## Optional creation

It is impossible to return `null` from a data object's `from` method since we always expect a data object when
calling `from`. To solve this, you can call the `optional` method:

```php
SongData::optional(null); // returns null
```

Underneath the optional method will call the `from` method when a value is given, so you can still magically create data
objects. When a null value is given, it will return null.

## Quickly getting data from Models, Requests, ...

By adding the `WithData` trait to a Model, Request or any class that can be magically be converted to a data object,
you'll enable support for the `getData` method. This method will automatically generate a data object for the object it
is called upon.

For example, let's retake a look at the `Song` model we saw earlier. We can add the `WithData` trait as follows:

```php
class Song extends Model{
    use WithData;
    
    protected $dataClass = SongData::class;
}
```

Now we can quickly get the data object for the model as such:

```php
Song::firstOrFail($id)->getData(); // A SongData object
```

We can do the same with a FormRequest, we don't use a property here to define the data class but use a method instead:

```php
class SongRequest extends Request
{
    use WithData;
    
    protected function dataClass(): string
    {
        return SongData::class;
    }
}
```

Now within a controller where the request is injected, we can get the data object like this:

```php
class SongController
{
    public function __invoke(SongRequest $request): SongData
    {
        $data = $request->getData();
    
        $song = Song::create($data);
        
        return $data;
    }
}
```

## Partial creation

Sometimes you have a data object with properties which shouldn't always be set, this can happen in a partial API update where you only want to update certain fields. In this case you can make a property `Undefined` as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string|Undefined $artist,
    ) {
    }
}
```

You can now create the data object as such:

```php
SongData::from([
    'title' => 'Never gonna give you up'
]);
```

The value of `artist` will automatically be set to `Undefined`. When you transform this data object to an array, it will look like this:

```php
[
    'title' => 'Never gonna give you up'
]
```

You can manually use `Undefined` values within magical creation methods as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string|Undefined $artist,
    ) {
    }
    
    public static function fromTitle(string $title): static{
        return new self($title, Undefined::create());
    }
}
```
