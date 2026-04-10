# Scoped Property Name Mapping

## Context

laravel-data's mapping attributes (`MapInputName`, `MapOutputName`, `MapName`) apply universally. This is limiting when data flows through different layers where naming conventions differ (e.g., `display_name` in Eloquent, `displayName` in API).

This adds an optional **scope** parameter. Fully backward compatible — mappings without scope always apply. Mappings with scope only apply when that scope is active.

The scope system uses a `Scope` class with two dimensions: `key` (which layer — used by mapping) and `context` (which action — usable by future features like validation contexts from PR #1149).

## API Design

```php
// Different mapping per scope (repeatable attributes)
// Class-string syntax (clean, when no context needed)
class UserData extends Data {
    #[MapInputName('display_name', scope: EloquentScope::class)]
    #[MapInputName('displayName', scope: RequestScope::class)]
    public string $displayName;
}

// Instance syntax (when you need context or custom key)
class UserData extends Data {
    #[MapInputName('display_name', scope: new EloquentScope())]
    #[MapInputName('displayName', scope: new RequestScope())]
    public string $displayName;
}

// Class-level scoped mapping
#[MapName(SnakeCaseMapper::class, scope: EloquentScope::class)]
class UserData extends Data {
    public string $firstName;  // maps to/from first_name only in Eloquent scope
}

// Custom scope (no subclass needed)
#[MapInputName('usr_id', scope: new Scope('stripe'))]
public string $userId;

// Auto-detected scopes:
$data = UserData::from($model);     // scope = new EloquentScope()
$data = UserData::from($request);   // scope = new RequestScope()
return $data;                        // scope = new ResourceScope()

// Explicit scope with context (for validation)
UserData::factory()->withScope(new RequestScope('store'))->from($request);
// Mapping matches on key='request'
// Validation (future) matches on context='store'

// No scope = always applies (backward compatible)
#[MapInputName('something')]
```

## Implementation Steps

### Step 1: Create Scope classes

**New file:** `src/Support/Scopes/Scope.php`
```php
class Scope
{
    public function __construct(
        public readonly string $key,
    ) {}
}
```

**New file:** `src/Support/Scopes/EloquentScope.php`
```php
class EloquentScope extends Scope
{
    public function __construct()
    {
        parent::__construct(self::getScopeKey());
    }

    public static function getScopeKey(): string
    {
        return 'eloquent';
    }
}
```

**New file:** `src/Support/Scopes/ApiScope.php` — base for all API-related scopes, carries context:
```php
class ApiScope extends Scope
{
    public function __construct(
        public readonly ?string $context = null,
    ) {
        parent::__construct(self::getScopeKey());
    }

    public static function getScopeKey(): string
    {
        return 'api';
    }
}
```

**New file:** `src/Support/Scopes/RequestScope.php`
```php
class RequestScope extends ApiScope
{
    public function __construct(?string $context = null)
    {
        parent::__construct($context);
        // Override key from parent
    }

    public static function getScopeKey(): string
    {
        return 'request';
    }
}
```

Note: since `ApiScope` constructor calls `self::getScopeKey()`, and PHP's `self` is resolved at definition time (not runtime), `RequestScope` needs to set its own key. Adjust constructors to use `static::getScopeKey()` or pass key explicitly:

```php
// ApiScope
public function __construct(public readonly ?string $context = null)
{
    parent::__construct(static::getScopeKey());
}
```

With `static::` (late static binding), subclasses automatically get their own key.

**New file:** `src/Support/Scopes/ResourceScope.php`
```php
class ResourceScope extends ApiScope
{
    public static function getScopeKey(): string
    {
        return 'resource';
    }
}
```

**Class hierarchy:**
```
Scope (concrete, custom key, no context)
├── EloquentScope (key='eloquent')
└── ApiScope (key='api', has context)
    ├── RequestScope (key='request', inherits context)
    └── ResourceScope (key='resource', inherits context)
```

**Inheritance-based matching** — a mapping for `ApiScope` matches `RequestScope` and `ResourceScope` too:
```php
#[MapName(SnakeCaseMapper::class, scope: ApiScope::class)]     // matches api, request, resource
#[MapInputName('special', scope: RequestScope::class)]          // only matches request (more specific)
```

**Context** lives on `ApiScope` and flows to children:
```php
new RequestScope('store')   // key='request', context='store'
new ResourceScope('admin')  // key='resource', context='admin'
new ApiScope('store')       // key='api', context='store'
new EloquentScope()         // key='eloquent', no context
new Scope('stripe')         // key='stripe', custom, no context
```

Checking scopes without magic strings:
```php
if ($scope->key === EloquentScope::getScopeKey()) { ... }
if ($scope instanceof ApiScope) { $scope->context; }  // context available on all API scopes
```

### Step 2: Update mapping attributes

**Files:** `src/Attributes/MapInputName.php`, `src/Attributes/MapOutputName.php`, `src/Attributes/MapName.php`

Changes for each:
- Add `Attribute::IS_REPEATABLE`
- Add `scope` constructor parameter: `?Scope $scope = null`
- Store the scope's key for later resolution

The `scope` parameter accepts a `Scope` instance or a class-string of a `Scope` subclass. Class-strings are resolved to instances at build time (in `NameMappersResolver`).

```php
// Instance (when you need context or custom key)
#[MapInputName('display_name', scope: new EloquentScope())]
#[MapInputName('usr_id', scope: new Scope('stripe'))]

// Class-string (cleaner when no context needed)
#[MapInputName('display_name', scope: EloquentScope::class)]
#[MapInputName('displayName', scope: RequestScope::class)]
```

The `scope` parameter also accepts an array for applying one mapping to multiple scopes:
```php
// Single scope
#[MapInputName('display_name', scope: EloquentScope::class)]

// Multiple scopes
#[MapInputName('display_name', scope: [EloquentScope::class, new Scope('legacy')])]
```

Parameter placement:
- `MapInputName`: `__construct(public string|int $input, Scope|string|array|null $scope = null)`
- `MapOutputName`: `__construct(public string|int $output, Scope|string|array|null $scope = null)`
- `MapName`: `__construct(public string|int|NameMapper $input, public string|int|NameMapper|null $output = null, Scope|string|array|null $scope = null)`

Where `string` is a `class-string<Scope>`, and `array` is `array<Scope|class-string<Scope>>`.

Each attribute stores the raw scopes as provided — no resolution in the constructor:
```php
/** @var array<Scope|class-string<Scope>>|null */
public readonly ?array $scopes;

// In constructor:
$this->scopes = match (true) {
    $scope === null => null,
    is_array($scope) => $scope,
    default => [$scope],
};
```

Resolution of class-strings to instances and key extraction happens in `NameMappersResolver` at build time.

### Step 3: Update `DataProperty`

**File:** `src/Support/DataProperty.php`

Add new readonly properties at end of constructor:
```php
/** @var array<string, string|int> key => mapped name */
public readonly array $scopedInputMappedNames = [],
/** @var array<string, string|int> key => mapped name */
public readonly array $scopedOutputMappedNames = [],
```

Add resolve methods that walk the scope class hierarchy (most specific first):
```php
public function resolveInputMappedName(?Scope $scope = null): string|int|null
{
    if ($scope !== null) {
        foreach (self::scopeKeyHierarchy($scope) as $key) {
            if (array_key_exists($key, $this->scopedInputMappedNames)) {
                return $this->scopedInputMappedNames[$key];
            }
        }
    }
    return $this->inputMappedName;
}

public function resolveOutputMappedName(?Scope $scope = null): string|int|null
{
    if ($scope !== null) {
        foreach (self::scopeKeyHierarchy($scope) as $key) {
            if (array_key_exists($key, $this->scopedOutputMappedNames)) {
                return $this->scopedOutputMappedNames[$key];
            }
        }
    }
    return $this->outputMappedName;
}

/** @return array<string> Keys from most specific to least specific */
private static function scopeKeyHierarchy(Scope $scope): array
{
    $keys = [$scope->key];
    $class = get_parent_class($scope);

    while ($class && $class !== Scope::class) {
        if (method_exists($class, 'getScopeKey')) {
            $keys[] = $class::getScopeKey();
        }
        $class = get_parent_class($class);
    }

    return $keys;
}
```

Example: active scope is `RequestScope` → checks keys `['request', 'api']` in order. A mapping for `ApiScope` is found as fallback when no `RequestScope` mapping exists.

### Step 4: Update `NameMappersResolver`

**File:** `src/Resolvers/NameMappersResolver.php`

Expand `execute()` return:
```php
return [
    'inputNameMapper' => $this->resolveInputNameMapper($attributes),
    'outputNameMapper' => $this->resolveOutputNameMapper($attributes),
    'scopedInputNameMappers' => $this->resolveScopedInputNameMappers($attributes),
    'scopedOutputNameMappers' => $this->resolveScopedOutputNameMappers($attributes),
];
```

**Update default resolution** to filter for null-scope attributes (native loops, no `collect()`):
```php
protected function resolveInputNameMapper(DataAttributesCollection $attributes): ?NameMapper
{
    $mapper = null;

    foreach ($attributes->all(MapInputName::class) as $attr) {
        if ($attr->scopes === null) {
            $mapper = $attr;
            break;
        }
    }

    if ($mapper === null) {
        foreach ($attributes->all(MapName::class) as $attr) {
            if ($attr->scopes === null) {
                $mapper = $attr;
                break;
            }
        }
    }

    if ($mapper) {
        return $this->resolveMapper($mapper->input);
    }

    return $this->resolveDefaultNameMapper(config('data.name_mapping_strategy.input'));
}
```

Same pattern for `resolveOutputNameMapper`.

**New scoped methods** (native array functions):
- Iterate `$attributes->all(MapInputName::class)`, filter non-null `scope`
- For each scope in `$attr->scopes`, resolve class-strings to instances (`is_string($s) ? new $s() : $s`), extract `->key`, resolve the mapper and store keyed by scope key
- Same for `MapName`, `MapOutputName`
- Config fallback: `data.name_mapping_strategy.scoped.{key}.input/output`
- Return `array<string, NameMapper>` keyed by scope key

### Step 5: Update `DataPropertyFactory`

**File:** `src/Support/Factories/DataPropertyFactory.php`

Update `build()` — add params after existing class mapper params:
```php
array $classScopedInputNameMappers = [],
array $classScopedOutputNameMappers = [],
```

After resolving defaults, resolve scoped names and pass to `DataProperty` constructor.

### Step 6: Update `DataClassFactory`

**File:** `src/Support/Factories/DataClassFactory.php`

In `resolveProperties()`, pass scoped class-level mappers from `$mappers` array to `propertyFactory->build()`.

### Step 7: Create `ScopeSuggestingNormalizer` interface on normalizers

**New file:** `src/Normalizers/ScopeSuggestingNormalizer.php`
```php
interface ScopeSuggestingNormalizer
{
    public function suggestedScope(): Scope;
}
```

**File:** `src/Normalizers/ModelNormalizer.php`
```php
class ModelNormalizer implements Normalizer, ScopeSuggestingNormalizer
{
    // ...existing normalize()...

    public function suggestedScope(): Scope
    {
        return new EloquentScope();
    }
}
```

**File:** `src/Normalizers/FormRequestNormalizer.php`
```php
class FormRequestNormalizer implements Normalizer, ScopeSuggestingNormalizer
{
    // ...existing normalize()...

    public function suggestedScope(): Scope
    {
        return new RequestScope();
    }
}
```

**File:** `src/Support/ResolvedDataPipeline.php` — track matched normalizer:
```php
public ?Normalizer $lastMatchedNormalizer = null;

public function normalize(mixed $value): array|Normalized
{
    foreach ($this->normalizers as $normalizer) {
        $properties = $normalizer->normalize($value);
        if ($properties !== null) {
            $this->lastMatchedNormalizer = $normalizer;
            break;
        }
    }
    // ...rest unchanged
}
```

### Step 8: Add `scope` to CreationContext + Factory

**File:** `src/Support/Creation/CreationContext.php`

Add mutable property:
```php
public ?Scope $scope = null,
```

**File:** `src/Support/Creation/CreationContextFactory.php`
```php
public ?Scope $scope = null;

public function withScope(Scope $scope): self
{
    $this->scope = $scope;
    return $this;
}
```

Update `get()`, `createFromCreationContext()` to pass/carry `scope`.

### Step 9: Add `scope` to TransformationContext + Factory

**File:** `src/Support/Transformation/TransformationContext.php`
```php
public ?Scope $scope = null,
```

**File:** `src/Support/Transformation/TransformationContextFactory.php`
```php
public ?Scope $scope = null;

public function withScope(Scope $scope): self
{
    $this->scope = $scope;
    return $this;
}
```

Update `get()` to pass `scope`.

### Step 10: Auto-detect scope in `DataFromSomethingResolver`

**File:** `src/Resolvers/DataFromSomethingResolver.php`

Add new method:
```php
protected function automaticallyResolveMapScope(
    CreationContext $creationContext,
    ResolvedDataPipeline $pipeline,
): void {
    if ($creationContext->scope !== null) {
        return;
    }

    if ($pipeline->lastMatchedNormalizer instanceof ScopeSuggestingNormalizer) {
        $creationContext->scope = $pipeline->lastMatchedNormalizer->suggestedScope();
    }
}
```

Call in `execute()` after normalization (after line 48), before any pipeline runs:
```php
$this->automaticallyResolveMapScope($creationContext, $pipeline);
```

Note: when user explicitly sets scope with context (e.g., `new RequestScope('store')`), auto-detection is skipped because `$creationContext->scope !== null`. The context survives.

### Step 11: Update runtime consumers

**`src/Support/ResolvedDataPipeline.php`** — `transformNormalizedToArray()` (line 89):
```php
$name = $creationContext->mapPropertyNames
    ? ($property->resolveInputMappedName($creationContext->scope) ?? $property->name)
    : $property->name;
```

**`src/DataPipes/MapPropertiesDataPipe.php`**:
```php
$inputMappedName = $dataProperty->resolveInputMappedName($creationContext->scope);
if ($inputMappedName === null) { continue; }
```

**`src/Resolvers/TransformedDataResolver.php`** (line 82+):
```php
if ($context->mapPropertyNames) {
    $outputMappedName = $property->resolveOutputMappedName($context->scope);
    if ($outputMappedName) {
        $name = $outputMappedName;
    }
}
```

**`src/DataPipes/InjectPropertyValuesPipe.php`** (line 23):
```php
$name = $dataProperty->resolveInputMappedName($creationContext->scope) ?: $dataProperty->name;
```

**`src/DataPipes/FillRouteParameterPropertiesDataPipe.php`** (line 34):
```php
$name = $dataProperty->resolveInputMappedName($creationContext->scope) ?: $dataProperty->name;
```

### Step 12: Auto-detect Resource scope on response

**File:** `src/Concerns/ResponsableData.php`

In `toResponse()`:
```php
$contextFactory = TransformationContextFactory::create()
    ->withWrapExecutionType(WrapExecutionType::Enabled)
    ->withScope(new ResourceScope());
```

### Step 13: Eloquent cast integration

**File:** `src/Support/EloquentCasts/DataEloquentCast.php`

In `get()`:
```php
return ($this->dataClass)::factory()
    ->withScope(new EloquentScope())
    ->from($payload);
```

In `set()`:
```php
$json = json_encode($value->transform(
    TransformationContextFactory::create()->withScope(new EloquentScope())
));
```

**File:** `src/Support/EloquentCasts/DataCollectionEloquentCast.php` — same pattern.

### Step 14: Update config

**File:** `config/data.php`
```php
'name_mapping_strategy' => [
    'input' => null,
    'output' => null,
    'scoped' => [
        // 'eloquent' => [
        //     'input' => \Spatie\LaravelData\Mappers\SnakeCaseMapper::class,
        //     'output' => \Spatie\LaravelData\Mappers\SnakeCaseMapper::class,
        // ],
    ],
],
```

### Step 15: Tests

**File:** `tests/MappingTest.php`:

1. `it('can map a property with scope when creating')`
2. `it('can map a property with scope when transforming')`
3. `it('falls back to default mapping when no scope-specific mapping exists')`
4. `it('uses different mappings for different scopes')`
5. `it('can use class-level scoped mapping')`
6. `it('ignores scoped mapping when no scope is active')`
7. `it('can use custom scope key')`
8. `it('auto-detects eloquent scope when creating from model')`
9. `it('auto-detects request scope when creating from request')`
10. `it('auto-detects resource scope in toResponse')`
11. `it('applies eloquent scope in DataEloquentCast')`
12. `it('scope context is preserved when explicitly set')`
13. `it('can use config-based scoped defaults')`
14. `it('existing mapping without scope is backward compatible')`

**File:** `tests/Resolvers/NameMappersResolverTest.php` — scoped resolver tests

### Step 16: Documentation

- `docs/advanced-usage/scopes.md` — **New dedicated doc** covering:
  - What scopes are, the Scope class hierarchy
  - Built-in scopes (EloquentScope, ApiScope, RequestScope, ResourceScope)
  - Custom scopes (`new Scope('stripe')`)
  - Auto-detection (Model → Eloquent, Request → Request, toResponse → Resource)
  - Scope context for validation (store, update, etc.)
  - Inheritance matching (ApiScope matches Request + Resource)
  - Using scopes with mapping attributes
  - Using scopes with validation rules
- `docs/as-a-data-transfer-object/mapping-property-names.md` — Link to scopes doc, brief examples
- `docs/as-a-resource/mapping-property-names.md` — Link to scopes doc, brief examples

## How validation attributes would work (future / PR #1149 integration)

Validation rules should specify their scope. They check `$scope->context` on API-related scopes:

```php
class UserData extends Data {
    // Mapping: depends on scope key
    #[MapInputName('display_name', scope: EloquentScope::class)]
    #[MapInputName('displayName', scope: ApiScope::class)]
    public string $displayName;

    // Validation: depends on scope context
    #[Required]                             // always required (no scope)
    public string $name;

    #[Required(scope: new RequestScope('store'))]  // only required in request+store
    #[Min(8, scope: new RequestScope('store'))]
    public ?string $password = null;
}

// Store: mapping matches 'request' → falls back to 'api' → uses 'displayName'
//        validation matches RequestScope with context 'store' → password required
UserData::factory()->withScope(new RequestScope('store'))->from($request);

// Update: mapping same, validation context is 'update' → password NOT required
UserData::factory()->withScope(new RequestScope('update'))->from($request);
```

Since `RequestScope extends ApiScope`, a validation rule scoped to `ApiScope('store')` would match both `RequestScope('store')` and `ResourceScope('store')` through inheritance.

## Files Summary

**New (6):**
- `src/Support/Scopes/Scope.php`
- `src/Support/Scopes/EloquentScope.php`
- `src/Support/Scopes/ApiScope.php`
- `src/Support/Scopes/RequestScope.php` (extends ApiScope)
- `src/Support/Scopes/ResourceScope.php` (extends ApiScope)
- `src/Normalizers/ScopeSuggestingNormalizer.php`

**Modified (~19):**
| File | Change |
|------|--------|
| `src/Attributes/MapInputName.php` | scope, IS_REPEATABLE |
| `src/Attributes/MapOutputName.php` | scope, IS_REPEATABLE |
| `src/Attributes/MapName.php` | scope, IS_REPEATABLE |
| `src/Support/DataProperty.php` | scoped arrays + resolve methods |
| `src/Resolvers/NameMappersResolver.php` | scoped resolution, native arrays |
| `src/Support/Factories/DataPropertyFactory.php` | resolve scoped names |
| `src/Support/Factories/DataClassFactory.php` | pass scoped class mappers |
| `src/Normalizers/ModelNormalizer.php` | implement ScopeSuggestingNormalizer |
| `src/Normalizers/FormRequestNormalizer.php` | implement ScopeSuggestingNormalizer |
| `src/Resolvers/DataFromSomethingResolver.php` | automaticallyResolveMapScope() |
| `src/Support/Creation/CreationContext.php` | add scope |
| `src/Support/Creation/CreationContextFactory.php` | add withScope() |
| `src/Support/Transformation/TransformationContext.php` | add scope |
| `src/Support/Transformation/TransformationContextFactory.php` | add withScope() |
| `src/DataPipes/MapPropertiesDataPipe.php` | resolveInputMappedName(scope) |
| `src/DataPipes/InjectPropertyValuesPipe.php` | resolveInputMappedName(scope) |
| `src/DataPipes/FillRouteParameterPropertiesDataPipe.php` | resolveInputMappedName(scope) |
| `src/Support/ResolvedDataPipeline.php` | scope-aware name resolution |
| `src/Resolvers/TransformedDataResolver.php` | resolveOutputMappedName(scope) |
| `src/Concerns/ResponsableData.php` | withScope(ResourceScope) |
| `src/Support/EloquentCasts/DataEloquentCast.php` | EloquentScope |
| `src/Support/EloquentCasts/DataCollectionEloquentCast.php` | EloquentScope |
| `config/data.php` | scoped config key |

**Unchanged** (use default mapping, no scope context):
- `DataValidationRulesResolver`, `DataValidationMessagesAndAttributesResolver`
- `DataMorphClassResolver`, `DataTypeScriptTransformer`, `EmptyDataResolver`

## Verification

1. `pest` — all existing tests pass (backward compatibility)
2. `pest tests/MappingTest.php` — new scoped tests
3. `pest tests/Resolvers/NameMappersResolverTest.php` — resolver tests
4. `./vendor/bin/phpstan analyse` — static analysis passes
