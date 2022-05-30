# Upgrading

Because there are many breaking changes an upgrade is not that easy. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

## From v1 to v2

High impact changes

- Please check the most recent `data.php` config file and change yours accordingly
- The `Cast` interface now has a `$context` argument in the `cast` method
- By default, it is now impossible to include/exclude properties using a request parameter. This can be manually overwritten.
- The `validate` method on a data object will not create a data object after validation, use `validateAndCreate` instead
- `DataCollection` is now being split into a `DataCollection` and `PaginatedDataCollection`

Low impact changes

- If you were using the inclusion and exclusion trees, please update your code to use the partialTrees
- The `DataProperty` and `DataClass` structures are completely rewritten
- `DataProperty` types is removed in favor of `DataType`
- The `transform` method signature is updated on `Data` and `DataCollection`
- The `Lazy` class is now split into `DefaultLazy`, `ConditionalLazy` and `RelationalLazy` all implementing `Lazy`
- If you were directly using Resolvers, please take a look, they have changed a lot
- The `DataTypeScriptTransformer` is updated for this new version, if you extend this then please take a look
- The `DataTransformer` and `DataCollectionTransformer` now use a `WrapExecutionType`
