---
title: Pipeline
weight: 5
---

The data pipeline allows you to configure how data objects are constructed from a payload. In the previous chapter we
saw that a data object created from a payload will be first normalized into an array. This array is passed into the
pipeline.

The pipeline exists of multiple pipes which will transform the normalized data into a collection of property values
which can be passed to the data object constructor.

By default, the pipeline exists of the following pipes:

- **AuthorizedDataPipe** checks if the user is authorized to perform the request
- **MapPropertiesDataPipe** maps the names of properties
- **ValidatePropertiesDataPipe** validates the properties
- **DefaultValuesDataPipe** adds default values for properties when they are not set
- **CastPropertiesDataPipe** casts the values of properties

Each result of the previous pipe is passed on into the next pipe, you can define the pipes on an individual data object
as such:

```php
class SongData extends Data
{
    public function __construct(
        // ...
    ) {
    }

    public static function pipeline(): DataPipeline
    {
        return DataPipeline::create()
            ->into(static::class)
            ->through(AuthorizedDataPipe::class)
            ->through(MapPropertiesDataPipe::class)
            ->through(ValidatePropertiesDataPipe::class)
            ->through(DefaultValuesDataPipe::class)
            ->through(CastPropertiesDataPipe::class);
    }
}
```

Each pipe implements the `DataPipe` interface and should return a `Collection` of properties:

```php
interface DataPipe
{
    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection;
}
```

The `handle` method has several arguments:

- **payload** the non normalized payload
- **class** the `DataClass` object for the data
  object [more info](/docs/laravel-data/v3/advanced-usage/internal-structures)
- **properties** the key-value properties which will be used to construct the data object

When using a magic creation methods, the pipeline is not being used (since you manually overwrite how a data object is
constructed). Only when you pass in a request object a minimal version of the pipeline is used to authorize and validate
the request.

## Preparing data for the pipeline

Sometimes you need to make some changes to the payload after it has been normalized, but before they are sent into the data pipeline. You can do this using the `prepareForPipeline` method as follows: 

```php
class SongMetadata
{
    public function __construct(
        public string $releaseYear,
        public string $producer,
    ) {}
}

class Song extends Data
{
    public function __construct(
        public string $title,
        public SongMetadata $metadata,
    ) {}
    
    public static function prepareForPipeline(Collection $properties) : Collection
    {
        $properties->put('metadata', $properties->only(['release_year', 'producer']));
        
        return $properties;
    }
}
```

Now it is possible to create a data object as follows:

```php
$song = Song::from([
    'title' => 'Never gonna give you up',
    'release_year' => '1987',
    'producer' => 'Stock Aitken Waterman',
]);
```

## Extending the pipeline within your data class

Sometimes you want to send your payload first through a certain pipe without creating a whole new pipeline, this can be done as such:

```php
class Song extends Data
{
    public static function pipeline(): DataPipeline
    {
        return parent::pipeline()->firstThrough(GuessCasingForKeyDataPipe::class);
    }
}
```
