# Upgrading

Because there are many breaking changes an upgrade is not that easy. There are many edge cases this guide does not
cover. We accept PRs to improve this guide.

## From v3 to v4

The following things are required when upgrading:

**Dependency changes (Likelihood Of Impact: High)**

The package now requires Laravel 10 as a minimum.

**Data::collection removal (Likelihood Of Impact: High)**

We've removed the Data::collection method in favour of the collect method. The collect method will return as type what
it gets as a type, so passing in an array will return an array of data objects. With the second parameter the output
type can be overwritten:

```php
// v3 
SomeData::collection($items); // DataCollection

// v4
SomeData::collect($items); // array of SomeData

SomeData::collect($items, DataCollection::class); // DataCollection
```

If you were using the `$_collectionClass`, `$_paginatedCollectionClass` or `$_cursorPaginatedCollectionClass` properties
then take a look at the magic collect functionality on information about how to replace these.

If you want to keep the old behaviour, you can use the `WithDeprecatedCollectionMethod` trait and `DeprecatedData`
interface on your data objects which adds the collection method again, this trait will probably be removed in v5.

**Transformers (Likelihood Of Impact: High)**

If you've implemented a custom transformer, then add the new `TransformationContext` parameter to the `transform` method
signature.

```php
// v3
public function transform(DataProperty $property, mixed $value): mixed
{
    // ...
}

// v4
public function transform(DataProperty $property,mixed $value,TransformationContext $context): mixed
{
    // ...
}
```

**Casts (Likelihood Of Impact: High)**

If you've implemented a custom cast, then add the new $context `CreationContext` parameter to the `cast` method
signature and rename the old $context parameter to $properties.

```php
// v3
public function cast(DataProperty $property, mixed $value, array $context): mixed;
{
    // ...
}

// v4
public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
{
    // ...
}
```

**The transform method (Likelihood Of Impact: Medium)**

The transform method signature was changed to use a factory pattern instead of parameters:

```php
// v3
$data->transform(
    transformValues:true,
    wrapExecutionType:WrapExecutionType::Disabled,
    mapPropertyNames:true,
);

// v4
$data->transform(
    TransformationContextFactory::create()
        ->transformValues()
        ->wrapExecutionType(WrapExecutionType::Disabled)
        ->mapPropertyNames()
);
```

**Data Pipes (Likelihood Of Impact: Medium)**

If you've implemented a custom data pipe, then add the new `CreationContext` parameter to the `handle` method
signature and change the type of the $properties parameter from `Collection` to `array`. Lastly, return an `array` of
properties instead of a `Collection`.

```php
// v3
public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
{
    // ...
}

// v4
public function handle(mixed $payload, DataClass $class, array $properties, CreationContext $creationContext): array
{
    // ...
}

```

**Invalid partials (Likelihood Of Impact: Medium)**

Previously when trying to include, exclude, only or except certain data properties that did not exist the package
continued working. From now on an exception will be thrown, these exceptions can be silenced by
setting `ignore_invalid_partials` to `true` within the config file

**Validated payloads (Likelihood Of Impact: Medium)**

Previously when validating data, the data object was being created from the payload provided, not the validated payload.

This payload can differ once you start using `exclude` rules which will exclude certain properties after validation.

Be sure that these properties are now typed as `Optional` since they can be missing from the payload.

```php
// v3

class SomeData extends Data {
    #[ExcludeIf('excludeProperty', true)]
    public string $property;
    public bool $excludeProperty;
} 
// Providing ['property' => 'something', 'excludeProperty' => true] will result in both fields set

// v4

class SomeData extends Data {
    #[ExcludeIf('excludeProperty', true)]
    public string|Optional $property;
    public bool $excludeProperty;
}
// Providing ['property' => 'something', 'excludeProperty' => true] will result in only the excludeProperty field set, the property field will be optional
```

Also notice, nested data objects will use the `required` rule, Laravel validation will not include the nested array in the validated payload when this array is empty.

**Internal data structure changes (Likelihood Of Impact: Low)**

If you use internal data structures like `DataClass` and `DataProperty` then take a look at these classes, a lot as
changed and the creation process now uses factories instead of static constructors.

**Data interfaces and traits (Likelihood Of Impact: Low)**

We've split up some interfaces and traits, if you were manually using these on your own data implementation then take a
look what has changed:

- DataTrait was removed, you can easily build it yourself if required
- PrepareableData (interface and trait) were merged with BaseData
- An EmptyData (interface and trait) was added
- An ContextableData (interface and trait) was added

**ValidatePropertiesDataPipe (Likelihood Of Impact: Low)**

If you've used the `ValidatePropertiesDataPipe::allTypes` parameter to validate all types, then please use Spatie\LaravelData\Attributes\Validation\ExcludeIf;use Spatie\LaravelData\Optional;use the new
context when creating a data object to enable this or update your `data.php` config file with the new default.

**Removal of `withoutMagicalCreationFrom` (Likelihood Of Impact: Low)**

If you used the `withoutMagicalCreationFrom` method on a data object, the now use the context to disable magical data
object creation:

```php
// v3
SomeData::withoutMagicalCreationFrom($payload);

// v4
SomeData::factory()->withoutMagicalCreation()->from($payload);
```

**Partials (Likelihood Of Impact: Low)**

If you we're manually setting the `$_includes`, ` $_excludes`, `$_only`, `$_except` or `$_wrap`  properties on a data
object, these have now been removed. Instead, you should use the new DataContext and add the partial.

**Removal of `DataCollectableTransformer` and `DataTransformer` (Likelihood Of Impact: Low)**

If you were using the `DataCollectableTransformer` or `DataTransformer` then please use the `TransformedDataCollectableResolver` and `TransformedDataResolver` instead.

**Removal of `DataObject` and `DataCollectable` (Likelihood Of Impact: Low)**

If you were using the `DataObject` or `DataCollectable` interfaces then please replace the interfaces based upon the `Data` and `DataCollection` interfaces to your preference.

**ValidationPath changes (Likelihood Of Impact: Low)**

If you were manually constructing a `ValidationPath` then please make sure to use an array instead of a string or null for the root level.

**Some advice with this new version of laravel-data**

We advise you to take a look at the following things:

- If you've cached data structures, be sure to clear the cache
- Take a look within your data objects if `DataCollection`'s, `DataPaginatedCollection`'s
  and `DataCursorPaginatedCollection`'s can be replaced with regular arrays, Laravel Collections and Paginators making code a lot more readable
- Replace `DataCollectionOf` attributes with annotations, providing IDE completion and more info for static analyzers
- Replace some `extends Data` definitions with `extends Resource` or `extends Dto` for more minimal data objects
- When using `only` and `except` at the same time on a data object/collection, previously only the except would be
  executed. From now on, we first execute the except and then the only.

## From v2 to v3

Upgrading to laravel data shouldn't take long, we've documented all possible changes just to provide the whole context.
You probably won't have to do anything:

- Laravel 9 is now required
- validation is completely rewritten
    - rules are now generated based upon the payload provided, not what a payload possibly could be. This means rules
      can change depending on the provided payload.
    - When you're injecting a `$payload`, `$relativePayload` or `$path` parameter in a custom rules method in your data
      object, then remove this and use the new `ValidationContext`:

```php
class SomeData extends Data {
    public bool $strict;

    public string $property;

    public static function rules(ValidationContext $context): array
    {
        if ($context->payload['strict'] === true) {
            return [
                'property' => ['in:strict'],
            ];
        }

        return [];
    }
}
```

- The type of `$rules` in the RuleInferrer handle method changed from `RulesCollection` to `PropertyRules`
- RuleInferrers now take a $context parameter which is a `ValidationContext` in their handle method
- Validation attributes now keep track where they are being evaluated when you have nested data objects. Now field
  references are relative to the object and not to the root validated object
- Some resolvers are removed like: `DataClassValidationRulesResolver`, `DataPropertyValidationRulesResolver`
- The default order of rule inferrers has been changed
- The $payload parameter in the `getValidationRules` method is now required
- The $fields parameter was removed from the `getValidationRules` method, this now should be done outside of the package
- all data specific properties are now prefixed with _, to avoid conflicts with properties with your own defined
  properties. This is especially important when
  overwriting `$collectionClass`, `$paginatedCollectionClass`, `$cursorPaginatedCollectionClass`, be sure to add the
  extra _ within your data classes.
- Serialization logic is now updated and will only include data specific properties

## From v1 to v2

High impact changes

- Please check the most recent `data.php` config file and change yours accordingly
- The `Cast` interface now has a `$context` argument in the `cast` method
- The `RuleInferrer` interface now has a `RulesCollection` argument instead of an `array`
- By default, it is now impossible to include/exclude properties using a request parameter until manually specified.
  This behaviour can be overwritten by adding these methods to your data object:

```php
    public static function allowedRequestIncludes(): ?array
    {
        return null;
    }

    public static function allowedRequestOnly(): ?array
    {
        return null;
    }
```

- The `validate` method on a data object will not create a data object after validation, use `validateAndCreate` instead
- `DataCollection` is now being split into a `DataCollection`, `PaginatedDataCollection`
  and `CursorPaginatedDataCollection`

Low impact changes

- If you were using the inclusion and exclusion trees, please update your code to use the partialTrees
- The `DataProperty` and `DataClass` structures are completely rewritten
- `DataProperty` types is removed in favor of `DataType`
- The `transform` method signature is updated on `Data` and `DataCollection`
- The `Lazy` class is now split into `DefaultLazy`, `ConditionalLazy` and `RelationalLazy` all implementing `Lazy`
- If you were directly using Resolvers, please take a look, they have changed a lot
- The `DataTypeScriptTransformer` is updated for this new version, if you extend this then please take a look
- The `DataTransformer` and `DataCollectionTransformer` now use a `WrapExecutionType`
- The `filter` method was removed from paginated data collections
- The `through` and `filter` operations on a `DataCollection` will now happen instant instead of waiting for the
  transforming process
