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

Data can also be created from JSON strings:

```php
SongData::from('{"title" : "Never Gonna Give You Up","artist" : "Rick Astley"}');
```

The package will find the required properties within the model and use them to construct the data object.

Although the PHP 8.0 constructor properties look great in data objects, it is perfectly valid to use regular properties without a constructor like so:

```php
class SongData extends Data
{
    public string $title;
    public string $artist;
}
```

## Mapping property names

Sometimes the property names in the array from which you're creating a data object might be different. You can define another name for a property when it is created from array using attributes:

```php
class ContractData extends Data
{
    public function __construct(
        public string $name,
        #[MapInputName('record_company')]
        public string $recordCompany,
    ) {
    }
}
```

Creating the data object can now be done as such:

```php
SongData::from(['name' => 'Rick Astley', 'record_company' => 'RCA Records']);
```

Changing all property names in a data object to snake_case in the data the object is created from can be done as such:

```php
#[MapInputName(SnakeCaseMapper::class)]
class ContractData extends Data
{
    public function __construct(
        public string $name,
        public string $recordCompany,
    ) {
    }
}
```

You can also use the `MapName` attribute when you want to combine input (see [transforming data objects](https://spatie.be/docs/laravel-data/v3/as-a-resource/from-data-to-resource#mapping-property-names)) and output property name mapping:

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

It is also possible to use multiple arguments in a magical creation method:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function fromMultiple(string $title, string $artist): self
    {
        return new self($title, $artist);
    }
}
```

Now we can create the data object like this:

```php
SongData::from('Never gonna give you up', 'Rick Astley');
```

There are a few requirements to enable magical data object creation:

- The method must be **static and public**
- The method must **start with from**
- The method cannot be called **from**

When the package cannot find such a method for a type given to the data object's `from` method. Then the data object
will try to create itself from the following types:

- An *Eloquent model* by calling `toArray` on it
- A *Laravel request* by calling `all` on it
- An *Arrayable* by calling `toArray` on it
- An *array*

This list can be extended using extra normalizers, find more about it [here](https://spatie.be/docs/laravel-data/v3/advanced-usage/normalizers).

When a data object cannot be created using magical methods or the default methods, a `CannotCreateData`
exception will be thrown.

## Optional creation

It is impossible to return `null` from a data object's `from` method since we always expect a data object when
calling `from`. To solve this, you can call the `optional` method:

```php
SongData::optional(null); // returns null
```

Underneath the optional method will call the `from` method when a value is given, so you can still magically create data
objects. When a null value is given, it will return null.

## Creation without magic methods

You can ignore the magical creation methods when creating a data object as such:

```php
SongData::withoutMagicalCreationFrom($song);
```

## Advanced creation

Internally this package is using a pipeline to create a data object from something. This pipeline exists of steps which transform properties into a correct structure and it can be completely customized. You can read more about it [here](/docs/laravel-data/v3/advanced-usage/pipeline).

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
class SongRequest extends FormRequest
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
    
        $song = Song::create($data->toArray());
        
        return $data;
    }
}
```
