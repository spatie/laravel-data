---
title: Get data from a class quickly
weight: 15
---

By adding the `WithData` trait to a Model, Request or any class that can be magically be converted to a data object,
you'll enable support for the `getData` method. This method will automatically generate a data object for the object it
is called upon.

For example, let's retake a look at the `Song` model we saw earlier. We can add the `WithData` trait as follows:

```php
class Song extends Model
{
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
