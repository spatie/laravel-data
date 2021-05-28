# This is my package LaravelDataResource

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-data-resource.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-data-resource)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-data-resource/run-tests?label=tests)](https://github.com/spatie/laravel-data-resource/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-data-resource/Check%20&%20fix%20styling?label=code%20style)](https://github.com/spatie/laravel-data-resource/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-data-resource.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-data-resource)

---
This repo can be used as to scaffold a Laravel package. Follow these steps to get started:

1. Press the "Use template" button at the top of this repo to create a new repo with the contents of this laravel-data-resource
2. Run "./configure-laravel-data-resource.sh" to run a script that will replace all placeholders throughout all the files
3. Remove this block of text.
4. Have fun creating your package.
5. If you need help creating a package, consider picking up our <a href="https://laravelpackage.training">Laravel Package Training</a> video course.
---

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-data-resource.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-data-resource)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-data-resource
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Spatie\LaravelDataResource\LaravelDataResourceServiceProvider" --tag="laravel-data-resource-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Spatie\LaravelDataResource\LaravelDataResourceServiceProvider" --tag="laravel-data-resource-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

Data objects are structured entities within Sail that fullfill the role of a data transfer object and a resource.

They have a few advantages:

- One structure for both the resource and data
- Can be lazy (=only send required data needed)
- They are type safe
- TypeScript transformer knows exactly what to do with them

There are situations where the unified data objects aren't a good fit. For example when the structure of the DTO and resource differ too much or in the case where a DTO is required but a resource isn't. In such case you'd better create a seperate resource and/or dto.

## Creating Data objects

A data object extends from `Data` and looks like this:

```php
class ContentThemeData extends Data
{
    public function __construct(
        public string $name
    ) {
    }

    public static function create(ContentTheme $theme): self
    {
        return new self(
            $theme->name
        );
    }
}

```

In the constructor we define the properties associated with this data object. Each data object also should have a static `create` method that will create the object based upon a model. This is required for automatically creating collections of resources and logging activity.

## Using Data objects as dto

Since the data objects are just simple PHP objects with some extra methods added to them, you can use them like regular PHP dto's:

```php
$data = new ContentThemeResource('Hello world');
```

You probably going to create a dto when receiving data from a form in the frontend. There are going to be two points where this happens: when you create something and when you edit something. That's why we'll create the data object within the request:

```php
class ContentThemeRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
        ];
    }

    public function getData(): ContentThemeData
    {
        $validated = $this->validated();

        return new ContentThemeData(
            $validated['name']
        );
    }
}
```

This has two advantages:

- your validation rules and data objects will be created in the same class
- you can create the same data object in different requests with slightly different properties

Since PHP supports the spread operator, for simple data objects you could do the following:

```php
public function getData(): ContentThemeData
{
    return new ContentThemeData(...$this->validated());
}
```

## Using Data objects as resource

When creating a resource you'll probably have a model, so you can call the `create` method:

```php
ContentThemeData::create($this->contentTheme);
```

At the moment you're creating a new model, in this case the model is `null`, you can use the `empty` method:

```php
ContentThemeData::empty();
```

This will return an array that follows the structure of the data object, it is possible to change the default values within this array by providing them in the constructor of the data object:

 ```php
 class ContentThemeData extends Data
{
    public function __construct(
        public string $name = 'Hello world'
    ) {
    }

    public static function create(ContentTheme $theme): self
    {
        return new self(
            $theme->name
        );
    }
}
 ```

Or by passing defaults within the `empty` call:


```php
ContentThemeData::empty([
	'name' => 'Hello world',
]);
```

In your view models the `values method will now look like this:

```php
public function values(): ContentThemeData | array
{
    return $this->contentTheme
        ? ContentThemeData::create($this->contentTheme)
        : ContentThemeData::empty();
}
```

### Indexes

We already took a look at using data objects within create and edit pages, but index pages also need resources albeit a collection of resources. You can easily create a collection of resources as such:

```php
ContentThemeIndexData::collection($contentThemes);
```

Within the `index` method of your controller you now can do the following:

```php
public function index(ContentThemesIndexQuery $indexQuery)
{
	return ContentThemeIndexData::collection($indexQuery->paginate());
}
```

As you can see we provide the `collection` method a paginated collection, the data object is smart enough to create a paginated response from this with links to the next, previous, last, ... pages.

When you yust provide a collection or array of resources to the `collection` method of a data object, then it will just return a `DataCollection` which is just a simple collection of resources:

```php
ContentThemeIndexData::collection($contentThemes); // No pagination here
```

### endpoints

Each data object can also have endpoints, you define these within a seperate `endpoints` method:

```php
class ContentThemeIndexData extends Data
{
    public function __construct(
        public ContentThemeUuid $uuid,
        public string $name
    ) {
    }

    public static function create(ContentTheme $theme): self
    {
        return new self(
            $theme->getUuid(),
            $theme->name,
        );
    }

    public function endpoints(): array
    {
        return [
            'edit' => action([ContentThemesController::class, 'edit'], $this->uuid),
            'destroy' => action([ContentThemesController::class, 'destroy'], $this->uuid),
        ];
    }
}
```

When this object is transformed to a resource, an extra key will be present with the endpoints. Also TypeScript transformer is smart enough to understand which endpoints this data object has.

When creating this data object as an dto, no endpoints will be added. And when an `endpoints` method wasn't added to the data object, then the resource representation of the data object won't include an endpoints key.

### Using collections of data objects within data objects

When you have a data object which has a collection of data objects as a property, then always type this as a  `DataCollection` with an annotation for the resources within the collection:

```php
class ApplicationData extends Data
{
    public function __construct(
        /** @var \App\Data\ContentThemeData[] */
        public DataCollection $themes
    ) {
    }

    public static function create(Event $event): static
    {
        return new self(
            ContentThemeData::collection($app->themes)
        );
    }
}
```

This will make sure data objects can be lazy loaded and that activity logging works as expected.

### Resolving resources from Data objects

You can convert a data object to a resource(array) as such:

```php
ContentThemeData::create($theme)->toArray();
```

When you want an array representation of the data object without transforming the properties like converting the underlying resources into arrays, then you can use:


```php
ContentThemeData::create($theme)->toArray();
```

A data object is also `Responsable` so it can be returned in a controller:

```php
public function (ContentTheme $theme): ContentThemeData
{
    return ContentThemeData::create($theme;)
}
```

Collections of data objects are also `Responsable` and `toArray()` can be called on them.

## Lazy properties

Data objects support lazy properties by default, they allow you to only output certain properties when converting a data object to a resource. You create a lazy property as such:

```php
class ContentThemeData extends Data
{
    public function __construct(
        public string $name,
        public int | Lazy $usages,
    ) {
    }

    public static function create(ContentTheme $theme): self
    {
        return new self(
            $theme->name,
            Lazy::create(fn() => $theme->calculateUsages()),
        );
    }
}
```

Now when you output this data object as a resource, the `usages` property will be missing since it is quite expensive to calculate:

```php
ContentThemeData::create($theme)->toArray(); // missing usages
```

We can include `usages` in the resource as such:

```php
ContentThemeData::create($theme)->include('usages')->toArray(); // with usages
```

This will also work when we use a data object within a collection, let's take a look at the `ApplicationData` resource from earlier. This one has a `DataCollection` property with `ContentThemeResources` within it. We now can include the `usages` property for all these resources by using the dot notation:

```php
ContentThemeData::create($theme)->include('themes.usages')->toArray(); // with usages
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ruben Van Assche](https://github.com/rubenvanassche)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
