# Replace phpDocumentor with phpstan/phpdoc-parser

## Context

laravel-data depends on `phpdocumentor/reflection` (^6.0) which pulls in 6+ packages including `nikic/php-parser`. The actual usage is narrow: FQCN resolution (resolving short class names like `SimpleData` to their fully qualified names) and parsing `@template`/`@extends` docblock tags.

This replaces all phpDocumentor usage with:
- `phpstan/phpdoc-parser` for docblock parsing (new direct dependency, zero deps, tiny)
- `spatie/php-structure-discoverer`'s `UseDefinitionsResolver` and `UsageCollection` for FQCN resolution (already a dependency, even has a comment "This is a feature for laravel-data and typescript-transformer")

The TypeScript transformer files (`src/Support/TypeScriptTransformer/`) also use phpDocumentor but are being phased out with v2 and should not be touched.

## Current phpDocumentor usage

Four source files use phpDocumentor classes:

1. `src/Resolvers/ContextResolver.php` uses `Context`, `ContextFactory` to cache namespace/use-statement contexts per class
2. `src/Support/Annotations/CollectionAnnotationReader.php` uses `DocBlockFactory`, `TypeResolver`, `Context` to parse `@template`/`@extends` tags from collection class docblocks
3. `src/Support/Annotations/DataIterableAnnotationReader.php` uses `FqsenResolver` to resolve short class names to FQCNs
4. `src/Support/DataCollectionAnnotationReader.php` uses `FqsenResolver`, `ContextFactory` (dead code, not referenced anywhere)

All annotation results are cached when `data:cache-structures` runs, so the parsing only happens on cold boot and in dev.

## Changes

### ContextResolver

`src/Resolvers/ContextResolver.php` currently returns `phpDocumentor\Reflection\Types\Context`. Change it to cache and return `UsageCollection` from structure-discoverer instead. Callers already have the `ReflectionClass` for the namespace.

```php
class ContextResolver
{
    /** @var array<string, UsageCollection> */
    protected array $cache = [];

    public function execute(ReflectionProperty|ReflectionClass|ReflectionMethod $reflection): UsageCollection
    {
        $reflectionClass = $reflection instanceof ReflectionProperty || $reflection instanceof ReflectionMethod
            ? $reflection->getDeclaringClass()
            : $reflection;

        return $this->cache[$reflectionClass->getName()] ??= (new UseDefinitionsResolver())->execute(
            $reflectionClass->getFileName()
        );
    }
}
```

### DataIterableAnnotationReader

`src/Support/Annotations/DataIterableAnnotationReader.php` already uses regex for docblock parsing. Only `resolveFcqn()` needs updating to use `UsageCollection::findFcqnForIdentifier()` instead of `FqsenResolver::resolve()`.

### CollectionAnnotationReader

`src/Support/Annotations/CollectionAnnotationReader.php` is the biggest change. Replace `DocBlockFactory`/`TypeResolver` with phpstan/phpdoc-parser:

- Parse docblocks with `PhpDocParser` into a `PhpDocNode` AST
- Use `PhpDocNode::getTemplateTagValues()` for `@template` tags (`TemplateTagValueNode` has `$name` and `$bound`)
- Use `PhpDocNode::getExtendsTagValues()` for `@extends` tags (`ExtendsTagValueNode` has `$type` as `GenericTypeNode` with `$genericTypes` array)
- Use `UsageCollection::findFcqnForIdentifier()` for type resolution
- Remove `TypeResolver` from constructor

### Dead code removal

Delete `src/Support/DataCollectionAnnotationReader.php` (not referenced by any source file or test).

### composer.json

- Remove `"phpdocumentor/reflection": "^6.0"`
- Add `"phpstan/phpdoc-parser": "^2.0"`

### Tests

- `tests/Resolvers/ContextResolverTest.php`: assert `UsageCollection` return type, verify caching
- `tests/Support/Annotations/CollectionAnnotationReaderTest.php`: remove `TypeResolver` from Mockery spy constructor args
- `tests/Support/Annotations/DataIterableAnnotationReaderTest.php`: no changes expected

## Key classes from existing dependencies

- `Spatie\StructureDiscoverer\Support\UseDefinitionsResolver::execute(string $filename): UsageCollection`
- `Spatie\StructureDiscoverer\Collections\UsageCollection::findFcqnForIdentifier(string $identifier, string $namespace): string`
- `PHPStan\PhpDocParser\Parser\PhpDocParser` for parsing docblocks into AST
- `PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode::getTemplateTagValues()` and `getExtendsTagValues()`
- `PHPStan\PhpDocParser\Ast\Type\GenericTypeNode` with `$genericTypes` array
- `PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode` with `$name`
- `PHPStan\PhpDocParser\Ast\Type\UnionTypeNode` with `$types` array

## Verification

1. `composer update` succeeds
2. `pest --filter=ContextResolver` passes
3. `pest --filter=CollectionAnnotationReader` passes
4. `pest --filter=DataIterableAnnotationReader` passes
5. `pest --filter=CollectionAttributeWithAnotations` passes
6. Full `pest` suite passes
7. No phpDocumentor packages remain in production dependencies