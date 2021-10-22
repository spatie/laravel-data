---
title: From a request
weight: 5
---

Next to manually creating, creating data objects, it is also possible to create a data object by the values given in the request.

For example, let's say you send a POST request to an endpoint with the following data:

```json
{
    "song" : "Never gonna give you up",
    "artist" : "Rick Astley"
}
```

This package can automatically resolve a `SongData` object from these values by using the `SongData` class we saw in an earlier chapter:

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

You can now get the data object anywhere within your application as such:

```php
app(SongData::class);
```

Or inject it into your controller:

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

This works because the package will register a callback into the Laravel container that will be called when the container wants to resolve a data object. It will then look at the request values and try to make a data object out of it.

There's a lot more you can do with this package. It is possible to add validation rules for the requests that will become data objects. You can automatically generate validation rules for data properties, authorize the creation of a data object and much more! Let's dive into it.

## Mapping a request onto a data object

By default, the package will do a one to one mapping from request to the data object, which means that for each property within the data object, a value with the same key will be searched within the request values.

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

## Validating a request

When creating a data object from a request, the package can also validate the values from the request that will be used to construct the data object.

It is possible to add rules as attributes to properties of a data object:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Max(20)]
        public string $artist,
    ) {
    }
}
```

Now when you provide an artist with a length of more than 20 characters, the validation will fail just like it would when you created a custom request class for the endpoint.

We've created a comprehensive set of rule attributes. You can find a complete list [here](/docs/laravel-data/v1/advanced-usage/validation-attributes).

One special attribute is the Rule attribute. With it, you can write rules just like you would when creating a custom Laravel request:

```php
// using an array
#[Rule(['required', 'string'])] 
public string $property

// using a string
#[Rule('required|string')]
public string $property

// using multiple arguments
#[Rule('required', 'string')]
public string $property
```

It is also possible to write rules down in a dedicated method on the data object. This can come in handy when you want to construct a custom rule object which isn't possible with attributes:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'artist' => ['required', 'string'],
        ];
    }
}
```

It is even possible to use the validationAttribute objects within the `rules` method:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(): array
    {
        return [
            'title' => [new Required(), new StringType()],
            'artist' => [new Required(), new StringType()],
        ];
    }
}
```

### Automatically inferring rules for properties

Since we have such strongly typed data objects, we can infer some validation rules from them. Rule inferrers will take information about the type of the property and will create validation rules from that information.

Rule inferrers are configured in the `data.php` config file:

```php
/*
 * Rule inferrers can be configured here. They will automatically add
 * validation rules to properties of a data object based upon
 * the type of the property.
 */
'rule_inferrers' => [
    Spatie\LaravelData\RuleInferrers\NullableRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer::class,
],
```

By default, four rule inferrers are enabled:

- **NullableRuleInferrer** will add a `nullable` rule when the property is nullable
- **RequiredRuleInferrer** will add a `required` rule when the property is not nullable
- **BuiltInTypesRuleInferrer** will add a rules which are based upon the built-in php types:
    - An `int` or `float` type will add the `numeric` rule
    - A `bool` type will add the `boolean` rule
    - A `string` type will add the `string` rule
    - A `array` type will add the `array` rule
- **AttributesRuleInferrer** will make sure that rule attributes we described above will also add their rules

It is possible to write your rule inferrers. You can find more information [here](/docs/laravel-data/v1/advanced-usage/creating-a-rule-inferrer).

### Overwriting the validator

Before validating the values, it is possible to plugin into the validator. This can be done as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $validator->errors()->add('field', 'Something is wrong with this field!');
        });
    }
}
```

### Overwriting messages

It is possible to overwrite the error messages that will be returned when an error fails:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function messages(): array
    {
        return [
            'title.required' => 'A title is required',
            'artist.required' => 'An artist is required',
        ];
    }
}
```

### Overwriting attributes

In the default Laravel validation rules, you can overwrite the name of the attribute as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function attributes(): array
    {
        return [
            'title' => 'titel',
            'artist' => 'artiest',
        ];
    }
}
```

## Authorizing a request

Just like with Laravel requests, it is possible to authorize an action for certain people only:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function authorize(): bool
    {
        return Auth::user()->name === 'Ruben';
    }
}
```

If the method returns `false`, then an `AuthorizationException` is thrown.

## Validating a data object without request

It is also possible to validate values for a data object without using a request:

```php
SongData::validate(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']); // returns a SongData object
```

When the validation passes, a new data object is returned with the values. When the validation fails, a Laravel `ValidationException` is thrown.
