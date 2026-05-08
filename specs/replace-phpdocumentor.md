# Replace phpDocumentor with phpstan/phpdoc-parser

## Context

laravel-data depends on `phpdocumentor/reflection` (^6.0) which pulls in 6+ packages including `nikic/php-parser`. The actual usage is narrow: FQCN resolution (resolving short class names like `SimpleData` to fully qualified names) and parsing `@template`/`@extends` docblock tags.

There is a concrete bug forcing this issue. Issue [#1172](https://github.com/spatie/laravel-data/issues/1172): on PHP 8.4 with Pest already installed, `composer require spatie/laravel-data` fails because `phpdocumentor/reflection 6.x` (even 6.6.0) hard-pins `phpdocumentor/reflection-docblock ^5`, while phpunit's chain (transitively pulled by Pest) pins `reflection-docblock` to `6.0.3`. The conflict is unsolvable without `composer require -W`.

This replaces all phpDocumentor usage with:
- `phpstan/phpdoc-parser` for docblock parsing (new direct dependency, zero deps, tiny)
- `spatie/php-structure-discoverer`'s `UseDefinitionsResolver` and `UsageCollection` for FQCN resolution (already a dependency, even has a comment "This is a feature for laravel-data and typescript-transformer")

The TypeScript transformer files (`src/Support/TypeScriptTransformer/`) also use phpDocumentor but are being phased out with v2 and should not be touched.

## Current phpDocumentor usage

Four source files use phpDocumentor classes:

1. `src/Resolvers/ContextResolver.php` uses `Context`, `ContextFactory` to cache namespace/use-statement contexts per class.
2. `src/Support/Annotations/CollectionAnnotationReader.php` uses `DocBlockFactory`, `TypeResolver`, `Context` to parse `@template`/`@extends` tags from collection class docblocks.
3. `src/Support/Annotations/DataIterableAnnotationReader.php` uses `FqsenResolver` to resolve short class names to FQCNs.
4. `src/Support/DataCollectionAnnotationReader.php` uses `FqsenResolver`, `ContextFactory` (dead code, not referenced anywhere).

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

Callers also need the namespace to call `findFcqnForIdentifier($identifier, $namespace)`. Either:
- Add a second method `getNamespace(Reflection*): string` that returns `$reflectionClass->getNamespaceName()`, or
- Have callers pull the namespace from their own reflection (they all have it already).

### DataIterableAnnotationReader

`src/Support/Annotations/DataIterableAnnotationReader.php` already uses regex for docblock parsing. Only `resolveFcqn()` needs updating to use `UsageCollection::findFcqnForIdentifier()` instead of `FqsenResolver::resolve()`:

```php
protected function resolveFcqn(
    ReflectionProperty|ReflectionClass|ReflectionMethod $reflection,
    string $class
): ?string {
    $usages = $this->contextResolver->execute($reflection);
    $namespace = $this->namespaceFor($reflection);

    return ltrim($usages->findFcqnForIdentifier($class, $namespace), '\\');
}
```

### CollectionAnnotationReader

`src/Support/Annotations/CollectionAnnotationReader.php` is the biggest change. Replace `DocBlockFactory`/`TypeResolver` with phpstan/phpdoc-parser:

- Parse docblocks with `PhpDocParser` into a `PhpDocNode` AST.
- Use `PhpDocNode::getTemplateTagValues()` for `@template` tags (`TemplateTagValueNode` has `$name` and `$bound`).
- Use `PhpDocNode::getExtendsTagValues()` for `@extends` tags (`ExtendsTagValueNode` has `$type` as `GenericTypeNode` with `$genericTypes` array).
- Use `UsageCollection::findFcqnForIdentifier()` for type resolution.
- Remove `TypeResolver` from constructor.

Wrap the parser call in try/catch returning `null` for parity with `DocBlockFactory`'s tolerance of malformed docblocks.

Behavioral mapping (preserves all existing test fixtures):

| Old | New |
|---|---|
| `DocBlockFactory::createInstance()->create($comment, $context)` | `(new PhpDocParser(...))->parse(new TokenIterator((new Lexer($config))->tokenize($comment)))` |
| `$tag->getName() === '@template'` + regex `T of X` | `PhpDocNode::getTemplateTagValues()` -> `TemplateTagValueNode` with `->name` + `->bound` (already a `TypeNode`) |
| `@extends` regex `<Key, Value>` | `PhpDocNode::getExtendsTagValues()` -> `ExtendsTagValueNode->type` (a `GenericTypeNode`) -> `->genericTypes` |
| Union: split `(string)$tag` on `\|`, take first arm | Walk `UnionTypeNode->types`, resolve each arm, take first |
| `$this->typeResolver->resolve($name, $context)` | `$usages->findFcqnForIdentifier($name, $namespace)` |

Note: `phpstan/phpdoc-parser` v2 requires `new ParserConfig(usedAttributes: [])` (was implicit in v1). Pass `[]` since we don't need line/comment attributes on AST nodes.

### Dead code removal

Delete `src/Support/DataCollectionAnnotationReader.php` (not referenced by any source file or test).

### composer.json

```diff
     "require" : {
         "php" : "^8.1",
         "illuminate/contracts" : "^10.0|^11.0|^12.0|^13.0",
-        "phpdocumentor/reflection" : "^6.0",
+        "phpstan/phpdoc-parser" : "^2.0",
         "spatie/laravel-package-tools" : "^1.9.0",
         "spatie/php-structure-discoverer" : "^2.0"
     },
```

`phpstan/phpdoc-parser ^2.0` is already on the lock graph transitively via the existing structure-discoverer dep. Promoting it to a direct dep makes the use explicit. We get the other phpdocumentor packages out via the loss of the `phpdocumentor/reflection` meta dep.

### Tests

- `tests/Resolvers/ContextResolverTest.php`: assert `UsageCollection` return type, verify caching.
- `tests/Support/Annotations/CollectionAnnotationReaderTest.php`: remove `TypeResolver` from Mockery spy constructor args.
- `tests/Support/Annotations/DataIterableAnnotationReaderTest.php`: no changes expected.

## Key classes from existing dependencies

- `Spatie\StructureDiscoverer\Support\UseDefinitionsResolver::execute(string $filename): UsageCollection`
- `Spatie\StructureDiscoverer\Collections\UsageCollection::findFcqnForIdentifier(string $identifier, string $namespace): string`
- `PHPStan\PhpDocParser\Parser\PhpDocParser` for parsing docblocks into AST
- `PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode::getTemplateTagValues()` and `getExtendsTagValues()`
- `PHPStan\PhpDocParser\Ast\Type\GenericTypeNode` with `$genericTypes` array
- `PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode` with `$name`
- `PHPStan\PhpDocParser\Ast\Type\UnionTypeNode` with `$types` array
- `PHPStan\PhpDocParser\ParserConfig` (required first arg to `Lexer`/`PhpDocParser`/`TypeParser`/`ConstExprParser` in v2)

## Verification

1. **Reproduce the original bug** before patching to confirm the failure mode:
   ```
   mkdir /tmp/ld-repro && cd /tmp/ld-repro
   composer init -n --name=tmp/repro
   composer require pestphp/pest:^4.3
   composer config repositories.local path /Users/ruben/Spatie/laravel-data
   composer require spatie/laravel-data:* --no-interaction
   ```
   Expect: failure citing `reflection-docblock ^5` vs `6.0.3`.

2. **Verify the fix** in a fresh dir post-patch. The same sequence must succeed without `-W`. Inspect the lock to confirm zero `phpdocumentor/*` packages remain.

3. `composer update` succeeds in the package itself.

4. Targeted test runs pass:
   ```
   pest --filter=ContextResolver
   pest --filter=CollectionAnnotationReader
   pest --filter=DataIterableAnnotationReader
   pest --filter=CollectionAttributeWithAnotations
   ```

5. Full `pest` suite passes.

6. No phpDocumentor packages remain in production dependencies.

7. CI matrix includes PHP 8.4 + Pest 4.3 (the failing combo) without `-W`.

## Risks

1. **Static analyzers reflecting on `src/Support/TypeScriptTransformer/*` without `spatie/typescript-transformer` installed will error.** Those files keep their phpDocumentor imports (since the TS-transformer code is being phased out in v2, not migrated). They only autoload when wired up. Worth a CHANGELOG note.

2. **`phpstan/phpdoc-parser` parses `@extends Collection` (no generics) into `InvalidTagValueNode`, not `ExtendsTagValueNode`.** The existing regex returns `null` in this case too, so behavior is preserved by the `instanceof ExtendsTagValueNode` check.

3. **`UseDefinitionsResolver` returns all use statements in a file regardless of which namespace block they belong to.** Files with multiple `namespace` blocks (rare, banned in PSR-4) could conceivably misresolve. Not a real concern for typical Data classes.
