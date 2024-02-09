---
title: Factories
weight: 10
---

It is possible to automatically create data objects in all sorts of forms with this package. Sometimes a little bit more
control is required when a data object is being created. This is where factories come in.

Factories allow you to create data objects like before but allow you to customize the creation process.

For example, we can create a data object using a factory like this:

```php
SongData::factory()->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

Collecting a bunch of data objects using a factory can be done as such:

```php
SongData::factory()->collect(Song::all())
```

## Disable property name mapping

We saw [earlier](/docs/laravel-data/v4/as-a-data-transfer-object/mapping-property-names) that it is possible to map
property names when creating a data object from an array. This can be disabled when using a factory:

```php
ContractData::factory()->withoutPropertyNameMapping()->from(['name' => 'Rick Astley', 'record_company' => 'RCA Records']); // record_company will not be mapped to recordCompany
```

## Changing the validation strategy

By default, the package will only validate Requests when creating a data object it is possible to change the validation
strategy to always validate for each type:

```php
SongData::factory()->alwaysValidate()->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

Or completely disable validation:

```php
SongData::factory()->withoutValidation()->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

## Disabling magic methods

A data object can be created
using [magic methods](/docs/laravel-data/v4/as-a-data-transfer-object/creating-a-data-object.md#magical-creation) , this can be disabled
when using a factory:

```php
SongData::factory()->withoutMagicalCreation()->from('Never gonna give you up'); // Won't work since the magical method creation is disabled
```

It is also possible to ignore the magical creation methods when creating a data object as such:

```php
SongData::factory()->ignoreMagicalMethod('fromString')->from('Never gonna give you up'); // Won't work since the magical method is ignored
```

## Adding additional global casts

When creating a data object, it is possible to add additional casts to the data object:

```php
SongData::factory()->withCast('string', StringToUpperCast::class)->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

These casts will not replace the other global casts defined in the `data.php` config file, they will though run before
the other global casts. You define them just like you would define them in the config file, the first parameter is the
type of the property that should be cast and the second parameter is the cast class.

## Using the creation context

Internally the package uses a creation context to create data objects. The factory allows you to use this context manually, but when using the from method it will be used automatically.

It is possible to inject the creation context into a magical method by adding it as a parameter:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function fromModel(Song $song, CreationContext $context): self
    {
        // Do something with the context
    }
}
```

You can read more about creation contexts [here](/docs/laravel-data/v4/advanced-usage/pipeline.md).
