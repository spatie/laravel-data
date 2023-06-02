---
title: From a request
weight: 8
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

## Using validation

When creating a data object from a request, the package can also validate the values from the request that will be used
to construct the data object.

The package automatically infers rules for certain properties. For example, a `?string` property will automatically have the `nullable` and `string` rules.

Be aware, first the rules will be generated from the data object you're trying to create, then if the validation is successful a data object will be created with the validated data. This means validation will be run before a data object exists. 

It is possible to add extra rules as attributes to properties of a data object:

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

When you provide an artist with a length of more than 20 characters, the validation will fail just like it would when
you created a custom request class for the endpoint.

You can find a complete list of available rules [here](/docs/laravel-data/v3/advanced-usage/validation-attributes).

If you want to have full control over the rules, you can also define them in a dedicated `rules` method on the data object (see later).

### Referencing route parameters

Sometimes you need a value within your validation attribute which is a route parameter. 
Like the example below where the id should be unique ignoring the current id:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Unique('songs', ignore: new RouteParameterReference('song'))]
        public int $id,
    ) {
    }
}
```

If the parameter is a model and another property should be used, then you can do the following:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Unique('songs', ignore: new RouteParameterReference('song', 'uuid'))]
        public string $uuid,
    ) {
    }
}
```

### Referencing other fields

It is possible to reference other fields in validation attributes:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[RequiredUnless('title', 'Never Gonna Give You Up')]
        public string $artist,
    ) {
    }
}
```

These references are always relative to the current data object. So when being nested like this:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $album_name,
        public SongData $song,
    ) {
    }
}
```

The generated rules will look like this:

```php
[
    'album_name' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'song.title' => ['required', 'string'],
    'song.artist' => ['string', 'required_if:song.title,"Never Gonna Give You Up"'],
]
```

If you want to reference fields starting from the root data object you can do the following:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[RequiredUnless(new FieldReference('album', fromRoot: true), 'Whenever You Need Somebody')]
        public string $artist,
    ) {
    }
}
```

The rules will now look like this:

```php
[
    'album_name' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'song.title' => ['required', 'string'],
    'song.artist' => ['string', 'required_if:album_name,"Whenever You Need Somebody"'],
]
```

### Rule attribute

One special attribute is the `Rule` attribute. With it, you can write rules just like you would when creating a custom
Laravel request:

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

It is also possible to write rules down in a dedicated method on the data object. This can come in handy when you want
to construct a custom rule object which isn't possible with attributes:

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

By overwriting a property's rules within the `rules` method, no other rules will be inferred automatically anymore for that property.

> Always use the array syntax for defining rules and not a single string which spits the rules by | characters.
> This is needed when using regexes those | can be seen as part of the regex

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

Rules defined within the `rules` method will always overwrite automatically generated rules.

You can even add dependencies to be automatically injected:

```php
use SongSettingsRepository;

class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(SongSettingsRepository $settings): array
    {
        return [
            'title' => [new RequiredIf($settings->forUser(auth()->user())->title_required), new StringType()],
            'artist' => [new Required(), new StringType()],
        ];
    }
}
```

Sometimes a bit more context is required, in such case a `ValidationContext` parameter can be injected as such:
Additionally, if you need to access the data payload, you can use `$payload` parameter:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(ValidationContext $context): array
    {
        return [
            'title' => ['required'],
            'artist' => Rule::requiredIf($context->fullPayload['title'] !== 'Never Gonna Give You Up'),
        ];
    }
}
```

By default, the provided payload is the whole request payload provided to the data object. 
If you want to generate rules in nested data objects then a relative payload can be more useful:

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

class SongData extends Data
{
    public function __construct(
        public string $title,
        public ?string $artist,
    ) {
    }
    
    public static function rules(ValidationContext $context): array
    {
        return [
            'title' => ['required'],
            'artist' => Rule::requiredIf($context->payload['title'] !== 'Never Gonna Give You Up'),
        ];
    }
}
```

When providing such payload:

```php
[
    'title' => 'Best songs ever made',
    'songs' => [
        ['title' => 'Never Gonna Give You Up'],
        ['title' => 'Heroes', 'artist' => 'David Bowie'],
    ],
];
```

The rules will be:

```php
[
    'title' => ['string', 'required'],
    'songs' => ['present', 'array'],
    'songs.*.title' => ['string', 'required'],
    'songs.*.artist' => ['string', 'nullable'],
    'songs.*' => [NestedRules(...)],
]
```

It is also possible to retrieve the current path in the data object chain we're generating rules for right now by calling `$context->path`. In the case of our previous example this would be `songs.0` and `songs.1`;

Make sure the name of the parameter is `$context` in the `rules` method, otherwise no context will be injected.

## Mapping a request onto a data object

By default, the package will do a one to one mapping from request to the data object, which means that for each property
within the data object, a value with the same key will be searched within the request values.

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
            $request->input('title_of_song'), 
            $request->input('artist_name')
        );
    }
}
```

### Getting the data object filled with request data from anywhere

You can resolve a data object from the container.

```php
app(SongData::class);
```

We resolve a data object from the container, it's properties will allready be filled by the values of the request with matching key names.
If the request contains data that is not compatible with the data object, a validation exception will be thrown.

### Automatically inferring rules for properties

Since we have such strongly typed data objects, we can infer some validation rules from them. Rule inferrers will take
information about the type of the property and will create validation rules from that information.

Rule inferrers are configured in the `data.php` config file:

```php
/*
 * Rule inferrers can be configured here. They will automatically add
 * validation rules to properties of a data object based upon
 * the type of the property.
 */
'rule_inferrers' => [
    Spatie\LaravelData\RuleInferrers\SometimesRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\NullableRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer::class,
],
```

By default, four rule inferrers are enabled:

- **SometimesRuleInferrer** will add a `sometimes` rule when the property is optional
- **NullableRuleInferrer** will add a `nullable` rule when the property is nullable
- **RequiredRuleInferrer** will add a `required` rule when the property is not nullable
- **BuiltInTypesRuleInferrer** will add a rules which are based upon the built-in php types:
    - An `int` or `float` type will add the `numeric` rule
    - A `bool` type will add the `boolean` rule
    - A `string` type will add the `string` rule
    - A `array` type will add the `array` rule
- **AttributesRuleInferrer** will make sure that rule attributes we described above will also add their rules

It is possible to write your rule inferrers. You can find more
information [here](/docs/laravel-data/v3/advanced-usage/creating-a-rule-inferrer).

### Skipping validation

Sometimes you don't want properties to be automatically validated, for instance when you're manually overwriting the
rules method like this:

```php
class SongData extends Data
{
    public function __construct(
        public string $name,
    ) {
    }
    
    public static function fromRequest(Request $request): static{
        return new self("{$request->input('first_name')} {$request->input('last_name')}")
    }
    
    public static function rules(): array
    {
        return [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
        ];
    }
}
```

When a request is being validated, the rules will look like this:

```php
[
    'name' => ['required', 'string'],
    'first_name' => ['required', 'string'],
    'last_name' => ['required', 'string'],
]
```

We know we never want to validate the `name` property since it won't be in the request payload, this can be done as
such:

```php
class SongData extends Data
{
    public function __construct(
        #[WithoutValidation]
        public string $name,
    ) {
    }
}
```

Now the validation rules will look like this:

```php
[
    'first_name' => ['required', 'string'],
    'last_name' => ['required', 'string'],
]
```

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

### Overwriting other validation functionality

Next to overwriting the validator, attributes and messages it is also possible to overwrite the following functionality.

The redirect when a validation failed:

```php
class SongData extends Data
{
    // ...

    public static function redirect(): string
    {
        return action(HomeController::class);
    }
}
```

Or the route which will be used to redirect after a validation failed:

```php
class SongData extends Data
{
    // ...

    public static function redirectRoute(): string
    {
        return 'home';
    }
}
```

Whether to stop validating on the first failure:

```php
class SongData extends Data
{
    // ...

    public static function stopOnFirstFailure(): bool
    {
        return true;
    }
}
```

The name of the error bag:

```php
class SongData extends Data
{
    // ...

    public static function errorBag(): string
    {
        return 'never_gonna_give_an_error_up';
    }
}
```

### Using dependencies in overwritten functionality

You can also provide dependencies to be injected in the overwritten validator functionality methods like `messages`
, `attributes`, `redirect`, `redirectRoute`, `stopOnFirstFailure`, `errorBag`:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function attributes(
        ValidationAttributesLanguageRepository $validationAttributesLanguageRepository
    ): array
    {
        return [
            'title' => $validationAttributesLanguageRepository->get('title'),
            'artist' => $validationAttributesLanguageRepository->get('artist'),
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

## Validating a data object without request

It is also possible to validate values for a data object without using a request:

```php
SongData::validate(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']); 
```

This will either throw a `ValidationException` or return a validated version of the payload.

It is possible to return a data object when the payload is valid when calling:

```php
SongData::validateAndCreate(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']); 
```

## Retrieving validation rules for a data object

You can retrieve the validation rules a data object will generate as such:

```php
AlbumData::getValidationRules($payload);
```

This will produce the following array with rules:

```php
[
    'title' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'songs.*.title' => ['required', 'string'],
    'songs.*.artist' => ['required', 'string'],
]
```

## Payload requirement

We suggest always to provide a payload when generating validation rules. Because such a payload is used to determine which rules will be generated and which can be skipped.
