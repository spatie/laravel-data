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
composer require spatie/laravel-data
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Spatie\LaravelData\LaravelDataServiceProvider" --tag="laravel-data-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

A data object extends from `Data` and looks like this:

```php
class SongData extends Data
{
    public function __construct(
        public string $name,
        public string $artist,
    ) {
    }

    public static function create(Song $song): self
    {
        return new self(
            $song->name,
            $song->artist
        );
    }
}

```

In the constructor we define the properties associated with this data object, only public properties will be included when converting the object to an array.

Each data object also should have a static `create` method that will create the object based upon a model. This method will be called when the data object is created from a collection of models.

Now you can create the data object in multiple ways, one where you don't have a model yet:

```php
new SongData('Rick Astley', 'Never gonna give you up');
```

And one where you can use a model to create the data object:

```php
SongData::create(Song::first());
```

If you have a collection of models then you can create a collection of data objects as such:

```php
SongData::collection(Song::all());
```

### Converting a data object to an array

You can return a data object within a controller, it will automatically be converted to a JSON response:

```php
class SongsController
{
	public function show(Song $song)
	{
		return SongData::create($song);
	}
}
```

This will return:

```json
{
	name: 'Never gonna give you up',
	artist: 'Rick Astley'
}
```

You can also manually convert a data object to an array as such:

```php
SongData::create(Song::first())->toArray();
```

### Converting empty objects to an array

When you're creating a new model, you don't have the required data to fill your data object. But sometimes you want to provide a blueprint for the data required to create a new model. For example:

```json
{
	name: null,
	artist: null
}
```

You could make each property of the data object nullable like this:

```php
class SongData extends Data
{
    public function __construct(
        public ?string $name,
        public ?string $artist,
    ) {
    }

	...
}
```

This would work but we know as soon as our model is created, the properties won't be `null`.

In such case you could return an empty representation of the data object:

```php
class SongsController
{
	public function create()
	{
		return SongData::empty();
	}
}
```

This will output the following JSON:

```json
{
	name: null,
	artist: null
}
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
