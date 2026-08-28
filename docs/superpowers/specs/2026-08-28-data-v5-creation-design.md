# Laravel Data v5, creation process redesign

Status: draft for review
Date: 2026-08-28

## 1. Goals

v5 is a focused major release. It changes how data objects are created, nothing else. Four goals:

1. Remove the data pipeline. A fixed sequence of actions builds data objects. Users extend through hooks, not through custom pipes.
2. The actions work on an intermediate representation: a payload array holding the data and a structure array holding metadata (source keys for mapped properties, the class at each level, cast decisions).
3. Fix validation with mapped keys. Today a client can send the unmapped property name and its value reaches the object without being validated. Validation error keys are also inconsistent between inferred rules and user overrides.
4. Make creation faster. Replace the serialize-to-cache-store mechanism for DataClass structures with generated PHP files, one autoloadable class per data class, written by a deploy command and kept hot by OPcache.

## 2. Non-goals (deferred to later releases)

These items from the original v5 notes are explicitly out of scope:

* Removing Optional as a union type. Optional stays as it is.
* Removing the auto-null behavior for nullable properties. It becomes configurable instead (see section 9).
* Removing DataCollection, PaginatedDataCollection, CursorPaginatedDataCollection.
* Union and intersection type casting improvements.
* Castable interface support, WithIteratableCast, closure-based property defaults.
* Mapping scopes and per-normalized-type mapping keys (see specs/mapping-scopes.md).
* Computed property rework. Computed keeps its v4 behavior.
* Splitting up Lazy.
* The transformation side (toArray, transformers, wrapping, includes). Untouched.

## 3. Design principles

* Internal flows never call user-overridable methods. Public methods like `from()`, `validate()`, and `toArray()` are entry points that start an internal flow. Nothing inside the engine dispatches back through them.
* laravel-data validates what laravel-data builds. If a magic method builds the object, validation is the responsibility of that method. If the magic method returns an array, laravel-data takes over again, including validation.
* Inference fills empty slots. Explicit rules from the user are never dropped, merged, or deduplicated.
* Extension happens through hooks declared on the factory, or through magic methods declared on the class. Class-owned reshaping logic belongs in a magic method and travels with the class. Call-site tweaks belong in factory hooks.
* The Data* support classes (DataClass, DataProperty, DataMethod, and friends) contain only simple types: scalars, arrays, and other Data* objects. No closures, no container references, no live Cast or Transformer instances. Anything that needs the container becomes a lazy recipe (class-string plus constructor arguments) instantiated on first use and memoized.

## 4. The construction state

One mutable state object, working name `ConstructionState`, carries everything through a single creation flow. It owns:

* `payload`: a nested array shaped like the data. Collections use concrete indices (`posts.0.title`).
* `structure`: a nested array shaped like the class tree, deliberately compact. One node per data object, not per property. A node holds:
  * a class reference (class-string) for the data object at that level. For morphable classes this is the concrete class picked by morph resolution, which is payload-dependent and expensive to re-derive.
  * `mappings`: property name to source key, only for properties where the two differ. This decision is made once during Fill and reused everywhere. It cannot be safely re-derived later: after validation, `validated()` prunes keys, so re-checking the payload could give a different answer than Fill got.
  * `children`: nodes for nested data properties, keyed by property name. A collection property gets one shared item node. When morphable collection items resolve to different classes, the item node holds per-index class references.

Everything else is inferred at the moment it is needed from the payload plus the DataClass: whether a value is present (`array_key_exists`), whether an absent property has a default (the DataClass knows), which cast applies (the Cast action decides and applies in one go), and whether a subtree is already finished (the payload value is an instance of the target class). The structure stores only what is payload-dependent and expensive or unsafe to re-derive.
* `path`: the current position as an array of segments, not a dot string.
* the `CreationContext`.

Actions receive the state object and read or write through its accessor methods. Raw PHP references to the arrays are never passed between actions. The two arrays are not parallel trees: the payload mirrors the data (with indices), the structure mirrors the class tree (without indices). Translating a concrete error key like `posts.0.title` to its structure node means walking the segments and skipping numeric ones.

## 5. The creation flow

A fixed, hardcoded sequence of action classes with plain `execute()` methods, called directly. No container resolution in the hot path, no configurable order, no replaceable actions.

1. **Authorize.** If any payload is a Request and the validation strategy covers requests, call `authorize()`. This runs before the magic method exit on purpose. In v4 the validation hack meant `authorize()` effectively ran for Request payloads even when a magic method matched. Skipping it in v5 would silently drop an authorization check on upgrade, which is a security regression. Authorization always runs, magic method or not.
2. **Magic method exit.** Match magic methods against the raw payloads (matching needs original types like Model or Request). Matching keeps the v4 semantics: arity plus type, positional and named payloads, first matching method in definition order wins, CreationContext parameters are injected and skipped during matching. Outcomes:
   * The method returns an instance of the target data class: done. No validation, no mapping, no casting.
   * The method returns anything else: the value becomes the node's source and the flow continues normally, including validation. This is "option A" from the original notes, generalized: an array is the common case, but a `Normalized`, a model, or anything the normalizer chain accepts works identically, since Fill reads from sources. The returned value does not go through magic method matching again, so a method returning a payload of its own accepted type cannot loop. Returning null to mean "try the next method" is not supported; `accepts()` stays the single source of truth for matching.
3. **Normalize.** Resolve the root sources (section 6). Arrays stay arrays, requests, Arrayables, and JSON become plain arrays once, models become `NormalizedModel`, custom normalizers run with first non-null winning. No payload becomes an empty array, several payloads become a source list.
4. **Resolve morph.** For abstract property-morphable classes, pick the concrete class before anything property-related happens.
5. **Fill.** Walk the DataClass properties and build the full payload and structure trees, depth first. Per property: read the value (mapping rules in section 7, recording the source key in the node's mappings when it differs from the property name), run injection attributes, and recurse into nested data objects and collections (section 8). Defaults are not written into the payload; they are resolved after validation. The `prepareData` hook fires for the root and for every nested data node before its properties are filled. After Fill, no other step discovers new properties.
6. **Validate.** Only if the strategy says so. One action generates rules for the whole tree (section 10), hooks run, one validator runs. On success, `$validator->validated()` becomes the payload going forward. On failure, the exception is rethrown with error keys in source-key space (which is already what the validator produces, see section 10).
7. **Resolve absences.** One rule for every property slot without a value in the current payload, whether never sent, skipped, or removed by an `exclude_*` rule: use the PHP default if there is one, else Optional if the type allows it, else null if nullable and auto-null applies (section 9), else leave it missing and let Instantiate throw a clear error. Properties that were deliberately not validated keep their value from the Fill payload.
8. **Cast.** Walk the properties and run the cast decision per value. Cast precedence matches v4: property attribute cast, then creation context casts, then global casts. Nested data objects are not built here, only their scalar leaves are cast.
9. **Instantiate.** Build objects bottom up, nested objects first, straight into constructors. The `beforeCreation` and `afterCreation` hooks fire per data node (section 11). Skipped entirely when the context says CreateData off. That is how `validate()` works and where precognition stops.

`Data`, `Dto`, and `Resource` stop having different pipelines. There is one flow. The base classes ship different creation context defaults: `Data` validates requests, `Dto` and `Resource` default to validation off.

## 6. Normalization

Normalized objects are inputs to Fill and they die when Fill ends. The payload array is Fill's output; by validation time everything is a plain nested array.

Per node, a source is a plain array or a `Normalized` object. The root Fill call receives a list of sources as its parameter, a list of one for the common single-payload case. The list length comes straight from `from(...$payloads)`'s argument list and is never inferred from array shape, so a payload that happens to be a numeric list can never be mistaken for multiple payloads. Below the root, every call passes exactly one source. Fill reads one property at a time from its node's source:

* Plain array: key lookup. Requests, Arrayables, and JSON strings are converted to a plain array once at the root. No wrapper objects; an array is good enough there.
* `Normalized`: `getProperty()` per property. `NormalizedModel` is the main user and stays, because `Model::toArray()` is wrong for creation, not just slow: it stringifies date casts, strips hidden attributes, and triggers appends. Lazy per-property access on raw attributes and loaded relations is the correct read.

Nesting needs no path bookkeeping. When Fill hits a nested property on a model source, `getProperty()` returns the related model instance, and that instance becomes the child node's source. The position is implicit in the object reference being held; there is no path-from-root resolution.

Normalization is lazy per node. When a value read from a parent is not yet an array or `Normalized` (a related model, a nested Arrayable, a JSON string in a column), the child node runs it through the same normalizer chain on demand. One small `NormalizedModel` per nested model is the only allocation, and it buys never touching relations or attributes the data class does not declare. For the common model case (validation off), Fill only materializes declared properties into the payload array.

Two classes from the original v5 notes are not built. `EmptyNormalized`: an empty array does the job. `MultiNormalized`: multi-payload is a plain source list, and it exists only at the root. `from($a, $b)` is the single place where multiple payloads enter, and they mostly exist to feed magic methods with multiple values, which are matched in step 2 before any of this runs.

When no magic method matched, the source list collapses at the first property read. Per top-level property, Fill picks the winning source (later payloads win, null and Optional never overwrite, presence checked lazily per source) and takes its value wholesale. From that point down everything is single-source. This matches v4 exactly: v4 ran the pipeline once per payload and shallow-merged the resulting property arrays, so a property's value always came wholesale from one payload. There is no cross-source deep merging, in v4 or in v5. The lazy per-property winner pick gives the same semantics as an eager normalize-and-merge without materializing model sources.

The `Normalized` interface, the `Normalizer` interface, and the `data.normalizers` config stay unchanged.

## 7. Mapping, the fix

Reading a property value from the payload happens exactly once, during Fill, with this precedence:

1. The mapped key, if the property has one and the key is present. Mapped always wins.
2. Otherwise the property name itself. Using the property name stays valid.

Whichever key supplied the value is recorded in the node's mappings as the source key. Everything downstream (rules, messages, attributes, error keys, `validated()` extraction) uses the source key and nothing else. When neither key is present, the mapped key is recorded as the canonical source key, so a `required` error points at the key the client was supposed to send.

This fixes the v4 bug where `MapPropertiesDataPipe` copies the mapped key onto the property name, leaves both keys in the payload, and validation rules only cover the mapped key. In v5 the value that reaches the object is always the value that was validated, under the key it was validated with.

Support for multiple mapped keys per property is planned for a later release, not v5.

## 8. Nested data and magic methods

When a property is a data object or a collection of data objects, no new creation process starts. The Fill action pushes the property onto the path, creates the child structure node, and runs the same fill logic on the nested value. What a nested value can be:

* An array, or a model relation through the normalizer: filled normally, recursion continues.
* An existing data object: taken as finished. Later actions detect this because the payload value is an instance of the target class; no explicit marker is stored. No rules are generated for it, no casting happens on it.
* A value a nested magic method accepts: option A applies. A return of the target class sits in the payload as a finished instance, unvalidated. Any other return (array, `Normalized`, model) becomes the subtree's source and gets validated and cast like a normal payload.

`prepareForPipeline()` is removed. Its two replacements: a magic method that accepts an array and returns an array covers class-owned payload reshaping (same flow position, validation still runs afterward), and the `prepareData` hook covers call-site reshaping, firing per data node with the payload subtree, the class name, and the path.

## 9. Defaults, absence, and auto-null

A PHP default value is a declaration that the property is optional in the payload. When the value is absent:

* No inferred rules are generated for the property, so a default never causes a validation failure. This matches v4.
* `rules()` overrides still apply (v4 fix #1187 is preserved). Explicit rule attributes on a defaulted property are skipped when the value is absent, matching v4; this is documented.
* The default is applied after validation, during Resolve absences. Defaults are PHP-typed values (enum instances, Carbon instances, nested data objects) and must never enter the validator.

Auto-null for nullable properties becomes configurable:

* Default behavior is unchanged from v4: an absent value for a nullable property resolves to null.
* A config option flips the default globally to strict mode (absent stays absent, users declare `= null` themselves). Strict mode fits JSON clients that send explicit nulls.
* A class-level or property-level attribute overrides the config in either direction. The attribute wins over config so vendor packages shipping data classes can rely on their own declared behavior.

## 10. Validation

### One rule action

The five RuleInferrers and the `rule_inferrers` config key are removed. One action walks the structure and payload and produces the full rule set for the whole tree in one pass. All precedence logic that v4 spreads across `PropertyRules::removeType()`, `AttributesRuleInferrer`, and `RequiredRuleInferrer` lives in this one action.

The requiring-rule principle: inference only fills empty slots.

* Explicit requiring rules (`Required`, `RequiredIf`, `RequiredUnless`, ...) from attributes or `rules()` overrides are all kept, even several on one property. Multiple conditionals mean the union of their conditions.
* The inferred unconditional `required` is only added when the property has no explicit requiring rule, no default, is not nullable, and is not optional.
* An explicit `present` suppresses the inferred `required`. `sometimes` is only emitted by the generator itself for conditionally validated slots, so it cannot collide with user rules.

### Key spaces

Users write `rules()`, `messages()`, and `attributes()` in property-name space, the names they see in their class. The generator translates them to source keys via the structure before anything reaches Laravel. Inferred rules are generated in source-key space directly. Validation errors therefore leave the validator keyed by what the client actually sent. The structure supports translating keys in both directions where needed.

### Collections

`Rule::forEach` is removed. The full payload tree with real indices exists before validation, so the generator always produces concrete per-index rules. A `rules()` override that depends on payload values is evaluated per item, with that item's payload in its ValidationContext. This fixes `distinct` and removes the v4 re-keying hack.

### The validated payload

`$validator->validated()` becomes the payload for everything after validation. Consequences:

* `exclude_if`, `exclude_unless`, `exclude_with`, `exclude_without` now actually remove values from the constructed object (v4 threw the validated result away, so these rules silently did nothing).
* Keys that were never covered by rules do not survive into construction, except properties deliberately excluded from validation, which keep their Fill value.
* Properties removed by exclusion become absent and go through Resolve absences like any other absent value.

### Entry points and hooks

* `Data::validate($payload)` runs the normal flow with validation on and CreateData off, and returns what `$validator->validated()` returned. No casting happens, mirroring Laravel's `Request::validate()`.
* `Data::validateAndCreate($payload)` runs the flow with validation on.
* `getValidationRules()` stays, running the same flow with an early exit after rule generation.
* The static `withValidator()` method on data classes is removed, replaced by the `withValidator` factory hook.
* The static `redirectUrl()` and `errorBag()` methods stay. They are declarative configuration, not flow.

### Precognition

When a request is precognitive, the generated rule set is filtered to the keys in the Precognition-Validate-Only header (in source-key space, what the client sends), the validator runs, and the flow stops before Resolve absences. No object is built.

## 11. Hooks

Hooks are plain array properties on `CreationContext`, one array of closures per hook type, each with a registration method on the factory that appends. This allows several hooks per point, for example one added by a package and one by the user. CreationContext is created once per `from()` call and survives the whole tree, so no container or registry abstraction is needed.

Execution semantics: closures run in registration order. For transforming hooks (`prepareData`, `afterRules`, `afterValidation`, `beforeCreation`, `afterCreation`), each closure receives the previous closure's result. For `beforeRules`, the first closure returning non-null wins and inference is skipped; remaining closures do not run for that property.

The v5 hook set, in flow order:

1. `prepareData`: fires during Fill for the root and every nested data node. Receives the payload subtree, the class name, and the path. Returns the adjusted subtree.
2. `beforeRules`: per property. Receives the property (DataProperty, path, payload value). Returns rules to skip inference for that property, or null to let inference run.
3. `afterRules`: per property. Receives the inferred rules, may modify them.
4. `withValidator`: receives the built Validator instance before it runs.
5. `afterValidation`: receives the validated payload, may adjust it before Resolve absences. Mirrors `passedValidation()` on FormRequests.
6. `beforeCreation`: fires per data node right before its constructor runs, during Instantiate. Receives the final property values (after casting) and the class name, may adjust the values.
7. `afterCreation`: fires per data node right after construction. Receives the built object, may return a replacement.

Both creation hooks fire for the root and for nested data nodes, consistent with `prepareData`. They do not fire for objects built by magic methods or passed in as existing data objects; those subtrees are finished and the engine does not touch them.

Deliberately not in 5.0, designed to slot in later without churn: `beforeCast` and `afterCast` per property, closure-based property defaults, app-wide default hooks via config.

Together with the existing toggles (validation strategy, property name mapping, magical creation, ignored magic methods, optional values, auto-null override, `withCast`, `withCastCollection`), this is the whole extension story. No replaceable actions, no custom pipes.

## 12. Generated structure cache

### The artifact

`php artisan data:cache` discovers every data class (same php-structure-discoverer mechanism and `directories` config as v4), builds each DataClass once via reflection, and writes one PHP file per class to `bootstrap/cache/data/`. Each file defines a small generated class:

```php
namespace Spatie\LaravelData\Generated;

final class App_Data_UserData
{
    public static function get(): DataClass
    {
        return new DataClass(/* literal arguments */);
    }
}
```

Files embed a package version marker. A mismatch means the file is ignored and reflection runs, so a stale cache from an older package version can never poison a request. `php artisan data:clear` deletes the directory.

### Loading

The service provider registers an `spl_autoload_register` callback limited to the `Spatie\LaravelData\Generated\` prefix, mapping class names to files in `bootstrap/cache/data/`. `DataConfig::getDataClass()` resolves: in-memory memo, then `class_exists($generatedName)` (false means no cache file), then `::get()`, falling back to live reflection. No manifest file is needed; the class name is the lookup key and the autoloader's file check doubles as the cache-hit test.

The generated classes compose with `opcache.preload`. Preloading eliminates compilation and autoloading cost. The `get()` call itself still runs once per request per used class and is then memoized; object graphs cannot be shared across requests, that is the floor for any design that keeps real objects.

### Deploy-only convention

No staleness detection, no mtime checks, matching `route:cache` and `config:cache`. Run it on deploy, clear it in development. Dev and test runs without files use reflection per process, exactly like v4 with caching disabled.

### Exportability requirements

* DataClass and DataProperty constructors are pure assignment. No container, no config reads, no reflection at construction time.
* PHP guarantees attribute constructor arguments are constant expressions, so every attribute is regenerated as a literal `new` call from `ReflectionAttribute::getArguments()`, including enums and class constants.
* Casts and transformers declared via attributes export as literal `new` calls. Anything that genuinely needs the container becomes a lazy recipe, instantiated on first use and memoized.
* The `LazyDataStructureProperty` abstraction is removed. The generator writes the six derived fields (allowed includes and friends) as literals; the reflection path computes them eagerly.

### Removals

`DataStructureCache`, `CachedDataConfig`, the `data:cache-structures` command, and the `structure_caching.store` and `duration` config keys are removed. DataConfig itself no longer needs caching: with rule inferrers gone and casts lazy, building it from `config('data')` at boot is trivial.

### Risk

The generated-class approach still has to prove itself in practice (autoloader registration, version marker, a benchmark against v4's serialize mechanism and against plain reflection). It does not need to be the first implementation task; the design will be adjusted if problems show up during implementation.

## 13. Breaking changes summary

* `DataPipeline`, `ResolvedDataPipeline`, all DataPipes, and the per-class `pipeline()` override are removed. Custom pipes migrate to hooks or magic methods.
* All RuleInferrers and the `rule_inferrers` config key are removed. Custom inferrers migrate to `beforeRules` and `afterRules` hooks.
* Static `withValidator()` is removed, replaced by the factory hook.
* `prepareForPipeline()` is removed, replaced by an array-returning magic method or the `prepareData` hook.
* Magic methods no longer trigger automatic validation for Request payloads. If a magic method builds the object, validation is its responsibility. Returning anything other than the finished data object (an array, a `Normalized`, a model) opts back into the normal flow including validation.
* `$validator->validated()` becomes the construction payload: exclusion rules now take effect, unvalidated extra keys no longer reach the object.
* Validation of mapped properties: the value that reaches the object is always the value that was validated. Clients sending the unmapped property name get validated under that key.
* `rules()`, `messages()`, and `attributes()` overrides are interpreted in property-name space and translated. v4 code that keyed overrides by mapped names must switch to property names.
* `data:cache-structures` is replaced by `data:cache` and `data:clear`. The cache store mechanism is gone.
* The cast signature changes: a cast receives the DataProperty, the value, the ConstructionState (giving access to payload, structure, and current path), and the CreationContext. v4 passed the flat surrounding properties array; the state object replaces it.
* The deprecated `FillRouteParameterPropertiesDataPipe` and the dead `CreationContext::$mappedProperties` structure are removed.

Not breaking: the entire transformation side, DataCollections, Optional, Lazy, computed properties, magic method matching semantics, `getValidationRules()`, `redirectUrl()`, `errorBag()`, rule attributes, casts and transformers as declared today.

## 14. Acceptance test cases

Beyond the existing test suite (which covers unchanged behavior), the spec adds:

* Mapped key bug: a payload containing only the unmapped property name is validated under that name. A payload containing both keys uses and validates the mapped key.
* Validation error keys always match the keys the client sent, including inside nested collections.
* `exclude_if` and friends remove values from the constructed object.
* `distinct` works inside data collections (concrete per-index rules).
* Requiring rules: an explicit `RequiredIf` is never clobbered by inferred `required`; two explicit conditionals both survive.
* Defaults: absent value with default constructs with the default and produces no validation error; provided value is fully validated; `rules()` overrides apply regardless of defaults (#1187 regression test).
* GitHub issues as regression cases: #873, #647, #681, #1019.
* Auto-null matrix: config default, config strict, attribute overriding each, on both request and array payloads.
* Magic methods: object return skips validation, array return gets validated, authorize runs in both cases, nested magic methods behave the same.
* Precognition: rule filtering by header, no object built, correct 204 semantics through Laravel's machinery.
* Normalization: nested model relations are read lazily and only for declared properties, `toArray()` is never called on a model tree, hidden attributes and stringified date casts do not leak into creation, multi-payload merge semantics match v4 (later payloads override, null and Optional never overwrite).
* Generated cache: version mismatch falls back to reflection, cached and uncached runs produce identical DataClass structures (a full equality assertion over the discovery set).

## 15. Implementation notes

* Performance is a stated goal: build a small benchmark harness early (creation of a nested data object from array and request, with and without generated cache) and track it across the implementation.
* The existing `specs/mapping-scopes.md` and `specs/replace-phpdocumentor.md` remain separate efforts.
