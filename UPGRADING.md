# Upgrading

Because there are many breaking changes an upgrade is not that easy. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

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
