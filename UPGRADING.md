# Upgrading

Because there are many breaking changes an upgrade is not that easy. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

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
