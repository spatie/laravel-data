# Upgrading

Because there are many breaking changes an upgrade is not that easy. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

## From v3 to v4

The following things are required when upgrading:

- Laravel 10 is now required
- Start by going through your code and replace all static `SomeData::collection($items)` method calls with `SomeData::collect($items, DataCollection::class)`
  - Use `DataPaginatedCollection::class` when you're expecting a paginated collection
  - Use `DataCursorPaginatedCollection::class` when you're expecting a cursor paginated collection
  - For a more gentle upgrade you can also use the `WithDeprecatedCollectionMethod` trait which adds the collection method again, but this trait will be removed in v5
  - If you were using `$_collectionClass`, `$_paginatedCollectionClass` or `$_cursorPaginatedCollectionClass` then take a look at the magic collect functionality on information about how to replace these
- If you were manually working with `$_includes`, ` $_excludes`, `$_only`, `$_except` or `$_wrap` these can now be found within the `$_dataContext`
- We split up some traits and interfaces, if you're manually using these on you own data implementation then take a look what has changed
  - DataTrait (T) and PrepareableData (T/I) were removed
  - EmptyData (T/I) and ContextableData (T/I) was added
- If you were calling the transform method on a data object, a `TransformationContextFactory` or `TransformationContext` is now the only parameter you can pass
  - Take a look within the docs what has changed
- If you have implemented a custom `Transformer`, update the `transform` method signature with the new `TransformationContext` parameter
- If you have implemented a custom `Cast`
  - The `$castContext` parameter is renamed to `$properties` and changed it type from `array` to  `collection`
  - A new `$context` parameter is added of type `CreationContext`
- If you have implemented a custom DataPipe, update the `handle` method signature with the new `TransformationContext` parameter
- If you manually created `ValidatePropertiesDataPipe` using the `allTypes` parameter, please now use the creation context for this
- The `withoutMagicalCreationFrom` method was removed from data in favour for creation by factory
- If you were using internal data structures like `DataClass` and `DataProperty` then take a look at what has been changed
- The `DataCollectableTransformer` and `DataTransformer` were replaced with their appropriate resolvers
- If you've cached the data structures, be sure to clear the cache

We advise you to take a look at the following things:
- Take a look within your data objects if `DataCollection`'s, `DataPaginatedCollection`'s and `DataCursorPaginatedCollection`'s can be replaced with regular arrays, Laravel Collections and Paginator
- Replace `DataCollectionOf` attributes with annotations, providing IDE completion and more info for static analyzers
- Replace some `extends Data` definitions with `extends Resource` or `extends Dto` for more minimal data objects
- When using `only` and `except` at the same time on a data object/collection, previously only the except would be executed. From now on, we first execute the except and then the only.
## From v2 to v3

Upgrading to laravel data shouldn't take long, we've documented all possible changes just to provide the whole context. You probably won't have to do anything:

- Laravel 9 is now required
- validation is completely rewritten 
  - rules are now generated based upon the payload provided, not what a payload possibly could be. This means rules can change depending on the provided payload.
  - When you're injecting a `$payload`, `$relativePayload` or `$path` parameter in a custom rules method in your data object, then remove this and use the new `ValidationContext`:

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
  - Validation attributes now keep track where they are being evaluated when you have nested data objects. Now field references are relative to the object and not to the root validated object
  - Some resolvers are removed like: `DataClassValidationRulesResolver`, `DataPropertyValidationRulesResolver`
  - The default order of rule inferrers has been changed
  - The $payload parameter in the `getValidationRules` method is now required
  - The $fields parameter was removed from the `getValidationRules` method, this now should be done outside of the package 
- all data specific properties are now prefixed with _, to avoid conflicts with properties with your own defined properties. This is especially important when overwriting `$collectionClass`, `$paginatedCollectionClass`, `$cursorPaginatedCollectionClass`, be sure to add the extra _ within your data classes.
- Serialization logic is now updated and will only include data specific properties

## From v1 to v2

High impact changes

- Please check the most recent `data.php` config file and change yours accordingly
- The `Cast` interface now has a `$context` argument in the `cast` method
- The `RuleInferrer` interface now has a `RulesCollection` argument instead of an `array`
- By default, it is now impossible to include/exclude properties using a request parameter until manually specified. This behaviour can be overwritten by adding these methods to your data object:
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
- `DataCollection` is now being split into a `DataCollection`, `PaginatedDataCollection` and `CursorPaginatedDataCollection`

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
- The `through` and `filter` operations on a `DataCollection` will now happen instant instead of waiting for the transforming process
