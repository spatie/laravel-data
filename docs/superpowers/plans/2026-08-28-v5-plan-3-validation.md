# Data v5 Plan 3: Validation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the v5 validation stage as standalone actions (rule generation, validation, absence resolution) that operate on the `ConstructionState` produced by `FillAction`, plus the auto null configuration and the validation hooks.

**Architecture:** Three new actions in `src/Support/Creation/Actions/`. `GenerateRulesAction` walks the structure and payload trees in one pass and produces rules, messages, and attributes for the whole tree in original key space with concrete collection indices. `ValidateAction` assembles and runs one Laravel validator, applies the validation hooks, and replaces the state payload with `$validator->validated()` plus re-injected unvalidated values. `ResolveAbsencesAction` fills absent slots with defaults, Optional, or auto null after validation. Nothing is wired into the v4 flow; that is plan 4 (build and switch). Actions are constructed with `new` in tests, exactly like `FillAction` in plan 2.

**Tech Stack:** PHP 8.2+, Laravel validation, Pest. Run tests with the `pest` binary directly.

**Spec:** `docs/superpowers/specs/2026-08-28-data-v5-creation-design.md` (sections 9, 10, 11 are the authority for this plan; sections 5, 6, 8 give the surrounding flow).

## Global Constraints

* Run tests with `pest` directly (e.g. `pest tests/Support/Creation/Actions/GenerateRulesActionTest.php --compact`). Never `php artisan test`.
* Git commits: no Co-Authored-By lines, no mention of Claude.
* Spatie PHP guidelines: no `else`, early returns, typed properties, constructor property promotion, `?Type` nullable notation, curly braces always.
* The full suite must stay green after every task (1420 passed at plan start). phpstan (`vendor/bin/phpstan analyse`) has 7 pre-existing errors; do not add new ones.
* Do not modify the v4 pipeline, resolvers, or rule inferrers except where a task explicitly says so. They keep serving the live v4 flow until plan 6.
* Vocabulary: `$payloads` is what `from()` received. `$normalized` is what normalization made of it. First payload wins.

## Deferred decisions recorded here

* **Dotted mapped names** (`#[MapInputName('nested.something')]`, `#[MapInputName('1.0')]`): v4 supports them (see `tests/MappingTest.php:122-205`), v5 Fill currently does not (a dotted key is read as a literal array key and misses). Support is deferred to plan 4, where Instantiate lands and the v4 mapping tests force the issue at switch time. `GenerateRulesAction` keys rules by joining original keys with dots, which composes with dotted original keys once Fill writes them at their nested payload position, so nothing in this plan blocks that fix.
* **AutoLazy placeholder:** v4 `DefaultValuesDataPipe` writes a `'tbd'` placeholder for `AutoWhenLoadedLazy` properties on model payloads. That mechanism needs the cast stage and the model payload, so it moves to plan 4. `ResolveAbsencesAction` does not handle it.
* **GitHub issue regressions (#873, #647, #681, #1019) and end-to-end precognition (204 semantics):** these need the wired `from()` flow, so they are plan 4 acceptance tests.

---

### Task 1: Auto null attributes, config key, and resolver

**Files:**
- Create: `src/Attributes/AutoNull.php`
- Create: `src/Attributes/WithoutAutoNull.php`
- Create: `src/Support/Creation/AutoNullResolver.php`
- Modify: `config/data.php` (add `auto_null` key after `validation_strategy`)
- Modify: `src/Support/DataConfig.php` (constructor and `createFromConfig`)
- Test: `tests/Support/Creation/AutoNullResolverTest.php`

**Interfaces:**
- Produces: `AutoNullResolver::execute(DataProperty $property, DataClass $dataClass): bool`. Precedence: property attribute, then class attribute, then `DataConfig::$autoNull`. `WithoutAutoNull` beats `AutoNull` at the same level.
- Produces: `DataConfig::$autoNull` (readonly bool, defaults to true, read from `config('data.auto_null')`).
- Consumed by: Task 4 (strict mode `present` rule) and Task 9 (absence resolution).

Background from the spec (section 9): auto null on (the default, v4 behavior) means an absent value for a nullable property resolves to null. Auto null off (strict mode) means absent stays absent and, for nullable properties without a default, validation emits `present` plus `nullable` so the failure is a clear validation error instead of a construction crash.

- [ ] **Step 1: Write the failing tests**

```php
<?php

use Spatie\LaravelData\Attributes\AutoNull;
use Spatie\LaravelData\Attributes\WithoutAutoNull;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\AutoNullResolver;
use Spatie\LaravelData\Support\DataConfig;

function autoNullResolver(bool $configDefault = true): AutoNullResolver
{
    return new AutoNullResolver(new DataConfig(autoNull: $configDefault));
}

function autoNullDataClass(string $class): \Spatie\LaravelData\Support\DataClass
{
    return app(DataConfig::class)->getDataClass($class);
}

it('follows the config default when no attribute is present', function () {
    $subject = new class () extends Data {
        public ?string $name;
    };

    $dataClass = autoNullDataClass($subject::class);

    $property = $dataClass->properties->get('name');

    expect(autoNullResolver(configDefault: true)->execute($property, $dataClass))->toBeTrue()
        ->and(autoNullResolver(configDefault: false)->execute($property, $dataClass))->toBeFalse();
});

it('lets a property attribute override the config in both directions', function () {
    $subject = new class () extends Data {
        #[WithoutAutoNull]
        public ?string $strict;

        #[AutoNull]
        public ?string $loose;
    };

    $dataClass = autoNullDataClass($subject::class);

    expect(autoNullResolver(configDefault: true)->execute($dataClass->properties->get('strict'), $dataClass))->toBeFalse()
        ->and(autoNullResolver(configDefault: false)->execute($dataClass->properties->get('loose'), $dataClass))->toBeTrue();
});

it('lets a class attribute override the config and a property attribute override the class', function () {
    $subject = new #[WithoutAutoNull] class () extends Data {
        public ?string $name;

        #[AutoNull]
        public ?string $overridden;
    };

    $dataClass = autoNullDataClass($subject::class);

    expect(autoNullResolver(configDefault: true)->execute($dataClass->properties->get('name'), $dataClass))->toBeFalse()
        ->and(autoNullResolver(configDefault: true)->execute($dataClass->properties->get('overridden'), $dataClass))->toBeTrue();
});

it('reads the auto_null config key into DataConfig', function () {
    expect(DataConfig::createFromConfig(['auto_null' => false])->autoNull)->toBeFalse()
        ->and(DataConfig::createFromConfig([])->autoNull)->toBeTrue();
});
```

Note: `$dataClass->properties` is a Laravel Collection keyed by property name, so `->get('name')` works.

- [ ] **Step 2: Run the tests to verify they fail**

Run: `pest tests/Support/Creation/AutoNullResolverTest.php --compact`
Expected: FAIL, class `AutoNullResolver` not found.

- [ ] **Step 3: Implement the attributes, config key, DataConfig field, and resolver**

`src/Attributes/AutoNull.php`:

```php
<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class AutoNull
{
}
```

`src/Attributes/WithoutAutoNull.php`:

```php
<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class WithoutAutoNull
{
}
```

`src/Support/Creation/AutoNullResolver.php`:

```php
<?php

namespace Spatie\LaravelData\Support\Creation;

use Spatie\LaravelData\Attributes\AutoNull;
use Spatie\LaravelData\Attributes\WithoutAutoNull;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;

class AutoNullResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }

    public function execute(DataProperty $property, DataClass $dataClass): bool
    {
        if ($property->attributes->has(WithoutAutoNull::class)) {
            return false;
        }

        if ($property->attributes->has(AutoNull::class)) {
            return true;
        }

        if ($dataClass->attributes->has(WithoutAutoNull::class)) {
            return false;
        }

        if ($dataClass->attributes->has(AutoNull::class)) {
            return true;
        }

        return $this->dataConfig->autoNull;
    }
}
```

(Import `DataProperty` as well; the snippet omits repeats for brevity but the file must import `Spatie\LaravelData\Support\DataProperty`.)

`src/Support/DataConfig.php`: add a promoted readonly parameter with a default so existing `new DataConfig(...)` call sites keep working, and read the config key. The constructor currently ends with `protected array $resolvedDataPipelines = []`; add the new parameter before the array parameters so it can be passed by name, or after them with a default. Use a named default:

```php
    public function __construct(
        public readonly GlobalTransformersCollection $transformers = new GlobalTransformersCollection(),
        public readonly GlobalCastsCollection $casts = new GlobalCastsCollection(),
        public readonly array $ruleInferrers = [],
        public readonly DataClassMorphMap $morphMap = new DataClassMorphMap(),
        protected array $dataClasses = [],
        protected array $resolvedDataPipelines = [],
        public readonly bool $autoNull = true,
    ) {
    }
```

In `createFromConfig`, pass it: add `autoNull: $config['auto_null'] ?? true,` to the `new static(...)` call (switch that call to named arguments for the trailing parameter: keep the five positional arguments as they are and append `autoNull:` named).

`config/data.php`, after the `validation_strategy` entry:

```php
    /*
     * When a nullable property has no value in the payload, the package resolves it
     * to null automatically. Setting this option to false switches to strict mode,
     * absent values stay absent and nullable properties without a default get a
     * `present` validation rule. The AutoNull and WithoutAutoNull attributes
     * override this setting per class or per property.
     */
    'auto_null' => true,
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/AutoNullResolverTest.php --compact`
Expected: PASS (4 tests).

- [ ] **Step 5: Run the full suite**

Run: `pest --compact`
Expected: no failures.

- [ ] **Step 6: Commit**

```bash
git add src/Attributes/AutoNull.php src/Attributes/WithoutAutoNull.php src/Support/Creation/AutoNullResolver.php src/Support/DataConfig.php config/data.php tests/Support/Creation/AutoNullResolverTest.php
git commit -m "Add auto null configuration with attributes and resolver"
```

---

### Task 2: Validation hooks on CreationContext and the factory

**Files:**
- Modify: `src/Support/Creation/CreationContext.php`
- Modify: `src/Support/Creation/CreationContextFactory.php`
- Test: `tests/Support/Creation/ValidationHooksRegistrationTest.php`

**Interfaces:**
- Produces five new readonly array properties on `CreationContext`, each defaulting to `[]`, placed after `prepareDataHooks`:
  - `beforeValidationHooks`: `Closure(array $payload): array`, chained, fires once with the full assembled payload before rule generation.
  - `beforeRulesHooks`: `Closure(DataProperty $property, ValidationPath $path, mixed $value): ?array`, per property, first non null return wins and skips inference.
  - `afterRulesHooks`: `Closure(array $rules, DataProperty $property, ValidationPath $path, mixed $value): array`, per property, chained, receives the denormalized Laravel ready rules.
  - `withValidatorHooks`: `Closure(Validator $validator): void`, fires with the built validator before it runs.
  - `afterValidationHooks`: `Closure(array $validated): array`, chained, fires with the validated payload.
- Produces matching factory registration methods that append: `beforeValidationHook(Closure $closure): static`, `beforeRulesHook(...)`, `afterRulesHook(...)`, `withValidatorHook(...)`, `afterValidationHook(...)`.
- Consumed by: Tasks 4, 6, 7 (execution semantics are implemented and tested there; this task only builds registration and threading).

- [ ] **Step 1: Write the failing test**

```php
<?php

use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('registers validation hooks on the factory and threads them into the context', function () {
    $beforeValidation = fn (array $payload) => $payload;
    $beforeRules = fn ($property, $path, $value) => null;
    $afterRules = fn (array $rules, $property, $path, $value) => $rules;
    $withValidator = function ($validator) {
    };
    $afterValidation = fn (array $validated) => $validated;

    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->beforeValidationHook($beforeValidation)
        ->beforeRulesHook($beforeRules)
        ->afterRulesHook($afterRules)
        ->withValidatorHook($withValidator)
        ->afterValidationHook($afterValidation)
        ->get();

    expect($context->beforeValidationHooks)->toBe([$beforeValidation])
        ->and($context->beforeRulesHooks)->toBe([$beforeRules])
        ->and($context->afterRulesHooks)->toBe([$afterRules])
        ->and($context->withValidatorHooks)->toBe([$withValidator])
        ->and($context->afterValidationHooks)->toBe([$afterValidation]);
});

it('carries validation hooks into a nested creation context factory', function () {
    $hook = fn (array $payload) => $payload;

    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->beforeValidationHook($hook)
        ->get();

    $nested = CreationContextFactory::createFromCreationContext(SimpleData::class, $context)->get();

    expect($nested->beforeValidationHooks)->toBe([$hook]);
});

it('supports registering multiple hooks of the same type in order', function () {
    $first = fn (array $payload) => $payload;
    $second = fn (array $payload) => $payload;

    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->beforeValidationHook($first)
        ->beforeValidationHook($second)
        ->get();

    expect($context->beforeValidationHooks)->toBe([$first, $second]);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `pest tests/Support/Creation/ValidationHooksRegistrationTest.php --compact`
Expected: FAIL, unknown method `beforeValidationHook`.

- [ ] **Step 3: Implement**

Follow the exact pattern `prepareDataHooks` already uses in both classes (constructor property with `= []` default, factory property, fluent append method, and threading through `createFromConfig`, `createFromCreationContext`, and `get()`). Add the five properties to `CreationContext`'s constructor after `prepareDataHooks`, each with a docblock:

```php
        /** @var array<int, Closure(array<string, mixed>): array<string, mixed>> */
        public readonly array $beforeValidationHooks = [],
        /** @var array<int, Closure(DataProperty, ValidationPath, mixed): ?array> */
        public readonly array $beforeRulesHooks = [],
        /** @var array<int, Closure(array, DataProperty, ValidationPath, mixed): array> */
        public readonly array $afterRulesHooks = [],
        /** @var array<int, Closure(Validator): void> */
        public readonly array $withValidatorHooks = [],
        /** @var array<int, Closure(array<string, mixed>): array<string, mixed>> */
        public readonly array $afterValidationHooks = [],
```

Import `Spatie\LaravelData\Support\DataProperty`, `Spatie\LaravelData\Support\Validation\ValidationPath`, and `Illuminate\Validation\Validator` in both files for the docblocks. Factory methods:

```php
    /**
     * @param Closure(array<string, mixed> $payload): array<string, mixed> $closure
     */
    public function beforeValidationHook(Closure $closure): static
    {
        $this->beforeValidationHooks[] = $closure;

        return $this;
    }
```

(and the same shape for the four others, each appending to its own array).

- [ ] **Step 4: Run the test to verify it passes**

Run: `pest tests/Support/Creation/ValidationHooksRegistrationTest.php --compact`
Expected: PASS (3 tests).

- [ ] **Step 5: Run the full suite and commit**

Run: `pest --compact`, expect no failures. Then:

```bash
git add src/Support/Creation/CreationContext.php src/Support/Creation/CreationContextFactory.php tests/Support/Creation/ValidationHooksRegistrationTest.php
git commit -m "Add validation hook registration to the creation context"
```

---

### Task 3: Unresolved morphs no longer throw during Fill

**Files:**
- Modify: `src/Support/Creation/Actions/ResolveMorphedDataClassAction.php`
- Modify: `src/Support/Creation/Actions/FillAction.php` (the morph branch in `fillNode`)
- Modify: `tests/Support/Creation/Actions/ResolveMorphedDataClassActionTest.php`
- Modify: `tests/Support/Creation/Actions/FillActionTest.php`

**Interfaces:**
- Changes `ResolveMorphedDataClassAction::execute(...)` return type from `DataClass` to `?DataClass`. Null means the morph could not be resolved (missing discriminator without a default, or `morph()` returned null). `CannotCreateAbstractClass` is no longer thrown here.
- `FillAction::fillNode` keeps the abstract `DataClass` as the node class when resolution returns null and fills the abstract class's own properties (the morphable discriminators), so the offending discriminator value lands in the payload for validation to report.
- Consumed by: Task 5. Rule generation for an abstract node emits `EnsurePropertyMorphable` on the morphable properties, which always fails for an abstract class, reproducing v4's clean validation error. Plan 4's Instantiate throws `CannotCreateAbstractClass` when the node class is still abstract and validation did not run.

Why this change: v4 validation ran before creation, so a bad discriminator produced a validation error via `EnsurePropertyMorphable` (see `src/Resolvers/DataClassFromValidationPayloadResolver.php`, which falls back to the abstract class). In v5 Fill runs first; throwing there turns a client error into a 500 and also violates the "Fill never throws" rule from spec section 6. This amends a plan 2 pinned behavior; record the ruling in the ledger.

- [ ] **Step 1: Update the pinned tests to the new contract**

In `tests/Support/Creation/Actions/ResolveMorphedDataClassActionTest.php`, find the tests expecting `CannotCreateAbstractClass` (there are tests for a missing discriminator and for `morph()` returning null). Change them to expect a null return instead, for example:

```php
it('returns null when the morph discriminator is missing and has no default', function () {
    // same arrange as before
    expect($action->execute($context, $dataClass, [[]]))->toBeNull();
});
```

In `tests/Support/Creation/Actions/FillActionTest.php`, find the test asserting that filling an abstract morphable class with an unresolvable payload throws `CannotCreateAbstractClass`. Replace it with:

```php
it('keeps the abstract class as node class when the morph cannot be resolved', function () {
    $state = fillAction()->execute(
        fillContext(AbstractPropertyMorphableData::class),
        [['variant' => 'non-existing']]
    );

    expect($state->structure()['class'])->toBe(AbstractPropertyMorphableData::class)
        ->and($state->payload())->toBe(['variant' => 'non-existing']);
});
```

(Adjust the discriminator key and fake class names to whatever the existing test uses; `AbstractPropertyMorphableData`'s morphable property is named `variant` in the plan 2 tests. The payload expectation covers only the abstract class's own declared properties.)

- [ ] **Step 2: Run the two test files to verify the updated tests fail**

Run: `pest tests/Support/Creation/Actions/ResolveMorphedDataClassActionTest.php tests/Support/Creation/Actions/FillActionTest.php --compact`
Expected: FAIL, the action still throws.

- [ ] **Step 3: Implement**

In `ResolveMorphedDataClassAction::execute`, replace both `throw CannotCreateAbstractClass::morphClassWasNotResolved(...)` statements with `return null;`, change the return type to `?DataClass`, and drop the now unused `CannotCreateAbstractClass` import.

In `FillAction::fillNode`, the morph branch currently reassigns `$dataClass` unconditionally:

```php
        if ($dataClass->isAbstract && $dataClass->propertyMorphable) {
            $dataClass = $this->resolveMorphedDataClassAction->execute(
                $state->creationContext,
                $dataClass,
                $normalized
            ) ?? $dataClass;
        }
```

Nothing else changes; `setNodeClass($dataClass)` then records the abstract class and the property loop fills the abstract class's own properties.

- [ ] **Step 4: Run the tests and the full suite**

Run: `pest tests/Support/Creation --compact`, then `pest --compact`.
Expected: PASS everywhere.

- [ ] **Step 5: Commit**

```bash
git add src/Support/Creation/Actions/ResolveMorphedDataClassAction.php src/Support/Creation/Actions/FillAction.php tests/Support/Creation/Actions/ResolveMorphedDataClassActionTest.php tests/Support/Creation/Actions/FillActionTest.php
git commit -m "Keep the abstract class when a morph cannot be resolved during Fill"
```

---

### Task 4: GenerateRulesAction core, scalar properties

**Files:**
- Create: `src/Support/Creation/GeneratedRules.php`
- Create: `src/Support/Validation/PropertyRuleSet.php`
- Create: `src/Support/Creation/Actions/GenerateRulesAction.php`
- Modify: `tests/Pest.php` (shared helper)
- Test: `tests/Support/Creation/Actions/GenerateRulesActionTest.php`

**Interfaces:**
- Produces `GeneratedRules`: a mutable value object with three public array properties `rules`, `messages`, `attributes`, all keyed by dot path strings in original key space.
- Produces `PropertyRuleSet`: an ordered rule list that never deduplicates on add (unlike v4's `PropertyRules`), with `add(ValidationRule ...$rules): static`, `prepend(ValidationRule ...$rules): static`, `hasType(string $class): bool`, `all(): array`.
- Produces `GenerateRulesAction::__construct(DataConfig $dataConfig, RuleNormalizer $ruleNormalizer, RuleDenormalizer $ruleDenormalizer, AutoNullResolver $autoNullResolver)` and `execute(ConstructionState $state): GeneratedRules`.
- Produces the shared Pest helper `v5CreationState(string $dataClass, array $payloads, ?CreationContext $context = null): ConstructionState` in `tests/Pest.php`, used by every test file in this plan.
- Consumed by: Tasks 5, 6 extend `generateForNode`; Task 7's `ValidateAction` calls `execute`.

This task implements rule generation for scalar (non data related) properties at any single node, including the requiring rule principle, and the recursion skeleton. Nested objects and collections are Task 5; `rules()` overrides, messages, attributes, and hooks are Task 6.

The per property decision procedure (this is the heart of the spec's section 10 and replaces all five v4 RuleInferrers; their v4 sources are in `src/RuleInferrers/` for reference, do not modify them):

1. Skip the property when `validate === false`.
2. Original key: `$node['mappings'][$property->name] ?? $property->name` when a structure node exists, otherwise `$property->inputMappedName ?? $property->name` (subtrees never entered by Fill have no node).
3. Skip entirely when the property has a default value and the key is absent from the node payload (v4 parity; Task 6 adds the `rules()` override exception, #1187).
4. Explicit rules come from the property's `ValidationRule` attributes, normalized through the v4 `RuleNormalizer` (which turns one attribute into one or more `ValidationRule` objects). They are never dropped or merged. Two `RequiredIf` attributes both survive.
5. Inference fills empty slots. Every "unless present" check looks at the union of explicit attribute rules and the base rules passed in for containers:
   - `Sometimes` when the type is optional, unless an explicit `Sometimes` or an explicit requiring rule is present (v4 removed `Sometimes` when a requiring attribute arrived; here it is simply not added).
   - Strict auto null `Present`: when the type is nullable, the property has no default, and `AutoNullResolver` answers false, unless a `Present` or requiring rule is already there.
   - `Required` only when the property is not nullable, not optional, and neither the explicit rules nor the base rules contain `Nullable`, `Present`, or a `RequiringRule`.
   - `Nullable` when the type is nullable, unless already present.
   - Built in type rules, each only when no rule of that class is already among the explicit or base rules: `Numeric` for int, `StringType` for string, `BooleanType` for bool, `Numeric` for float, `ArrayType` for array, `Enum($enumClass)` when the type accepts a `BackedEnum`.
6. The final ordered set is assembled in three segments, matching v4's output order so plan 4's switch produces minimal test churn: first the inferred modifiers in the fixed order `Sometimes`, `Present` (strict), `Required`, `Nullable`; then the base rules and inferred built in type rules; then the explicit attribute rules. When the property is morphable, `new EnsurePropertyMorphable($dataClass)` is appended last, with the node's `DataClass` (concrete when the morph resolved, abstract when it did not, in which case the rule fails and produces the v4 style validation error).
7. Denormalize the whole set with the v4 `RuleDenormalizer` against the NODE path (not the property path; field references resolve relative to the node, v4 parity).

- [ ] **Step 1: Add the shared helper to `tests/Pest.php`**

Append under the Functions header:

```php
use Spatie\LaravelData\Support\Creation\Actions\FillAction;
use Spatie\LaravelData\Support\Creation\Actions\NormalizePayloadAction;
use Spatie\LaravelData\Support\Creation\Actions\ReadDataPropertyAction;
use Spatie\LaravelData\Support\Creation\Actions\ResolveMorphedDataClassAction;
use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataConfig;

function v5CreationState(string $dataClass, array $payloads, ?CreationContext $context = null): ConstructionState
{
    $fillAction = new FillAction(
        app(DataConfig::class),
        new NormalizePayloadAction(
            array_map(fn (string $normalizer) => app($normalizer), config('data.normalizers')),
        ),
        new ReadDataPropertyAction(),
        new ResolveMorphedDataClassAction(app(DataConfig::class), new ReadDataPropertyAction()),
    );

    return $fillAction->execute(
        $context ?? CreationContextFactory::createFromConfig($dataClass)->get(),
        $payloads
    );
}
```

(Place the `use` statements at the top of `tests/Pest.php`. The `fillAction()` helper in `FillActionTest.php` stays as it is.)

- [ ] **Step 2: Write the failing tests**

`tests/Support/Creation/Actions/GenerateRulesActionTest.php`:

```php
<?php

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\RequiredIf;
use Spatie\LaravelData\Attributes\Validation\RequiredUnless;
use Spatie\LaravelData\Attributes\WithoutAutoNull;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\Actions\GenerateRulesAction;
use Spatie\LaravelData\Support\Creation\AutoNullResolver;
use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\GeneratedRules;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Validation\RuleDenormalizer;
use Spatie\LaravelData\Support\Validation\RuleNormalizer;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

function generateRulesAction(): GenerateRulesAction
{
    return new GenerateRulesAction(
        app(DataConfig::class),
        app(RuleNormalizer::class),
        app(RuleDenormalizer::class),
        new AutoNullResolver(app(DataConfig::class)),
    );
}

function generateRules(string $dataClass, array $payload): GeneratedRules
{
    return generateRulesAction()->execute(v5CreationState($dataClass, [$payload]));
}

it('generates inferred rules for a simple data class', function () {
    $rules = generateRules(SimpleData::class, ['string' => 'Hello']);

    expect($rules->rules)->toEqual(['string' => ['required', 'string']]);
});

it('generates rules for typed properties', function () {
    $dataClass = new class () extends Data {
        public int $amount;
        public bool $flag;
        public ?string $name;
    };

    $rules = generateRules($dataClass::class, ['amount' => 1, 'flag' => true, 'name' => null]);

    expect($rules->rules)->toEqual([
        'amount' => ['required', 'numeric'],
        'flag' => ['required', 'boolean'],
        'name' => ['nullable', 'string'],
    ]);
});

it('keys rules by the original key recorded during Fill', function () {
    $mappedOnly = generateRules(SimpleDataWithMappedProperty::class, ['description' => 'Hello']);
    $propertyNameOnly = generateRules(SimpleDataWithMappedProperty::class, ['string' => 'Hello']);
    $both = generateRules(SimpleDataWithMappedProperty::class, ['description' => 'a', 'string' => 'b']);
    $absent = generateRules(SimpleDataWithMappedProperty::class, []);

    expect($mappedOnly->rules)->toHaveKey('description')
        ->and($propertyNameOnly->rules)->toHaveKey('string')
        ->and($both->rules)->toHaveKey('description')
        ->and($both->rules)->not->toHaveKey('string')
        ->and($absent->rules)->toHaveKey('description');
});

it('never drops explicit requiring rules and does not add an inferred required next to them', function () {
    $dataClass = new class () extends Data {
        #[RequiredIf('other', 'x'), RequiredUnless('other', 'y')]
        public string $conditional;

        public string $other;
    };

    $rules = generateRules($dataClass::class, ['other' => 'x']);

    expect($rules->rules['conditional'])->toEqual([
        'string',
        'required_if:other,x',
        'required_unless:other,y',
    ]);
});

it('suppresses the inferred required when an explicit present rule exists', function () {
    $dataClass = new class () extends Data {
        #[Present]
        public string $name;
    };

    $rules = generateRules($dataClass::class, ['name' => 'x']);

    expect($rules->rules['name'])->toContain('present')
        ->and($rules->rules['name'])->not->toContain('required');
});

it('keeps explicit attribute rules next to inferred type rules', function () {
    $dataClass = new class () extends Data {
        #[Max(20)]
        public string $name;
    };

    $rules = generateRules($dataClass::class, ['name' => 'x']);

    expect($rules->rules['name'])->toEqual(['required', 'string', 'max:20']);
});

it('skips properties with a default when the value is absent and validates them when present', function () {
    $dataClass = new class () extends Data {
        public string $name = 'default';
        public string $other;
    };

    $absent = generateRules($dataClass::class, ['other' => 'x']);
    $present = generateRules($dataClass::class, ['name' => 'y', 'other' => 'x']);

    expect($absent->rules)->not->toHaveKey('name')
        ->and($present->rules)->toHaveKey('name');
});

it('skips properties excluded from validation', function () {
    $dataClass = new class () extends Data {
        #[\Spatie\LaravelData\Attributes\WithoutValidation]
        public string $skipped;

        public string $name;
    };

    $rules = generateRules($dataClass::class, ['skipped' => 'x', 'name' => 'y']);

    expect($rules->rules)->not->toHaveKey('skipped');
});

it('emits present plus nullable for a strict nullable property without default', function () {
    $dataClass = new class () extends Data {
        #[WithoutAutoNull]
        public ?string $name;
    };

    $rules = generateRules($dataClass::class, []);

    expect($rules->rules['name'])->toEqual(['present', 'nullable', 'string']);
});

it('does not emit present for a strict nullable property with a default', function () {
    $dataClass = new class () extends Data {
        #[WithoutAutoNull]
        public ?string $name = null;
    };

    $rules = generateRules($dataClass::class, []);

    expect($rules->rules)->not->toHaveKey('name');
});
```

Notes for the implementer: rule order inside a property follows the three segment assembly from the procedure above (modifiers, then base and type rules, then explicit attribute rules); the expected arrays in the tests are authoritative. `SimpleDataWithMappedProperty` maps `string` to `description`. If an expected array turns out to differ only in ORDER from what a correct implementation produces, adjust the implementation, not the expectation, until both match the procedure; report a blocker if the procedure itself seems wrong.

- [ ] **Step 3: Run the tests to verify they fail**

Run: `pest tests/Support/Creation/Actions/GenerateRulesActionTest.php --compact`
Expected: FAIL, `GenerateRulesAction` not found.

- [ ] **Step 4: Implement**

`src/Support/Creation/GeneratedRules.php`:

```php
<?php

namespace Spatie\LaravelData\Support\Creation;

class GeneratedRules
{
    /**
     * @param array<string, array<int, mixed>> $rules
     * @param array<string, string> $messages
     * @param array<string, string> $attributes
     */
    public function __construct(
        public array $rules = [],
        public array $messages = [],
        public array $attributes = [],
    ) {
    }
}
```

`src/Support/Validation/PropertyRuleSet.php`:

```php
<?php

namespace Spatie\LaravelData\Support\Validation;

class PropertyRuleSet
{
    /** @var array<int, ValidationRule> */
    protected array $rules = [];

    public function add(ValidationRule ...$rules): static
    {
        array_push($this->rules, ...$rules);

        return $this;
    }

    public function prepend(ValidationRule ...$rules): static
    {
        $this->rules = [...$rules, ...$this->rules];

        return $this;
    }

    public function hasType(string $class): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /** @return array<int, ValidationRule> */
    public function all(): array
    {
        return $this->rules;
    }
}
```

`src/Support/Creation/Actions/GenerateRulesAction.php`:

```php
<?php

namespace Spatie\LaravelData\Support\Creation\Actions;

use BackedEnum;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Support\Creation\AutoNullResolver;
use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\GeneratedRules;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\EnsurePropertyMorphable;
use Spatie\LaravelData\Support\Validation\PropertyRuleSet;
use Spatie\LaravelData\Support\Validation\RequiringRule;
use Spatie\LaravelData\Support\Validation\RuleDenormalizer;
use Spatie\LaravelData\Support\Validation\RuleNormalizer;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Support\Validation\ValidationRule;

class GenerateRulesAction
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected RuleNormalizer $ruleNormalizer,
        protected RuleDenormalizer $ruleDenormalizer,
        protected AutoNullResolver $autoNullResolver,
    ) {
    }

    public function execute(ConstructionState $state): GeneratedRules
    {
        $generated = new GeneratedRules();

        $structure = $state->structure();

        $this->generateForNode(
            $state,
            $this->dataConfig->getDataClass($structure['class']),
            $structure,
            $state->payload(),
            ValidationPath::create(),
            $generated,
        );

        return $generated;
    }

    /**
     * @param ?array{class: ?string, mappings: array<string, string>, children: array<string, array>} $node
     */
    protected function generateForNode(
        ConstructionState $state,
        DataClass $dataClass,
        ?array $node,
        mixed $nodePayload,
        ValidationPath $path,
        GeneratedRules $generated,
    ): void {
        foreach ($dataClass->properties as $property) {
            if ($property->validate === false) {
                continue;
            }

            $originalKey = $this->originalKey($state, $property, $node);
            $propertyPath = $path->property((string) $originalKey);
            $hasValue = is_array($nodePayload) && array_key_exists($originalKey, $nodePayload);
            $value = $hasValue ? $nodePayload[$originalKey] : null;

            if ($property->hasDefaultValue && ! $hasValue) {
                continue;
            }

            $generated->rules[$propertyPath->get()] = $this->generatePropertyRules(
                $state,
                $dataClass,
                $property,
                $value,
                $path,
            );
        }
    }

    protected function originalKey(
        ConstructionState $state,
        DataProperty $property,
        ?array $node
    ): string|int {
        if ($node !== null) {
            return $node['mappings'][$property->name] ?? $property->name;
        }

        if ($state->creationContext->mapPropertyNames && $property->inputMappedName !== null) {
            return $property->inputMappedName;
        }

        return $property->name;
    }

    /**
     * @param array<int, ValidationRule> $baseRules
     *
     * @return array<int, mixed>
     */
    protected function generatePropertyRules(
        ConstructionState $state,
        DataClass $dataClass,
        DataProperty $property,
        mixed $value,
        ValidationPath $nodePath,
        array $baseRules = [],
    ): array {
        $explicit = new PropertyRuleSet();

        foreach ($property->attributes->all(ValidationRule::class) as $attribute) {
            $explicit->add(...$this->ruleNormalizer->execute($attribute));
        }

        $probe = new PropertyRuleSet();
        $probe->add(...$explicit->all());
        $probe->add(...$baseRules);

        $rules = new PropertyRuleSet();

        if (
            $property->type->isOptional
            && ! $probe->hasType(Sometimes::class)
            && ! $probe->hasType(RequiringRule::class)
        ) {
            $rules->add(new Sometimes());
        }

        if (
            $property->type->isNullable
            && ! $property->hasDefaultValue
            && ! $probe->hasType(Present::class)
            && ! $probe->hasType(RequiringRule::class)
            && ! $this->autoNullResolver->execute($property, $dataClass)
        ) {
            $rules->add(new Present());
        }

        if ($this->shouldInferRequired($property, $probe, $rules)) {
            $rules->add(new Required());
        }

        if ($property->type->isNullable && ! $probe->hasType(Nullable::class)) {
            $rules->add(new Nullable());
        }

        $rules->add(...$baseRules);

        $this->addBuiltInTypeRules($property, $probe, $rules);

        $rules->add(...$explicit->all());

        if ($property->morphable) {
            $rules->add(new EnsurePropertyMorphable($dataClass));
        }

        return $this->ruleDenormalizer->execute($rules->all(), $nodePath);
    }

    protected function shouldInferRequired(
        DataProperty $property,
        PropertyRuleSet $probe,
        PropertyRuleSet $inferred
    ): bool {
        if ($property->type->isNullable || $property->type->isOptional) {
            return false;
        }

        if ($probe->hasType(Nullable::class) || $inferred->hasType(Nullable::class)) {
            return false;
        }

        if ($probe->hasType(Present::class) || $inferred->hasType(Present::class)) {
            return false;
        }

        if ($probe->hasType(RequiringRule::class)) {
            return false;
        }

        return true;
    }

    protected function addBuiltInTypeRules(
        DataProperty $property,
        PropertyRuleSet $probe,
        PropertyRuleSet $rules
    ): void {
        $type = $property->type->type;

        if (($type->acceptsType('int') || $type->acceptsType('float')) && ! $probe->hasType(Numeric::class)) {
            $rules->add(new Numeric());
        }

        if ($type->acceptsType('string') && ! $probe->hasType(StringType::class)) {
            $rules->add(new StringType());
        }

        if ($type->acceptsType('bool') && ! $probe->hasType(BooleanType::class)) {
            $rules->add(new BooleanType());
        }

        if ($type->acceptsType('array') && ! $probe->hasType(ArrayType::class)) {
            $rules->add(new ArrayType());
        }

        $enumClass = $type->findAcceptedTypeForBaseType(BackedEnum::class);

        if ($enumClass !== null && ! $probe->hasType(Enum::class)) {
            $rules->add(new Enum($enumClass));
        }
    }
}
```

The `$baseRules` parameter and the required suppression via `Present` exist for Task 5's container rules; they are inert for scalars in this task. The v4 rule order within a property (`required` and `nullable` first, then type rules, then attribute rules as they denormalize) is what the test expectations encode; check against `pest tests/ValidationTest.php` style expectations if unsure and report discrepancies rather than papering over them.

- [ ] **Step 5: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/Actions/GenerateRulesActionTest.php --compact`
Expected: PASS.

- [ ] **Step 6: Run the full suite and commit**

Run: `pest --compact`, expect no failures. Then:

```bash
git add src/Support/Creation/GeneratedRules.php src/Support/Validation/PropertyRuleSet.php src/Support/Creation/Actions/GenerateRulesAction.php tests/Pest.php tests/Support/Creation/Actions/GenerateRulesActionTest.php
git commit -m "Generate scalar validation rules from the construction state"
```

---

### Task 5: GenerateRulesAction, nested data objects and collections

**Files:**
- Modify: `src/Support/Creation/Actions/GenerateRulesAction.php`
- Test: `tests/Support/Creation/Actions/GenerateRulesActionTest.php` (append)

**Interfaces:**
- Consumes: everything Task 4 produced.
- Produces the full tree walk. Nested objects recurse with the child structure node; collections produce concrete per index rules (no `Rule::forEach`, no wildcards in rule keys); finished subtrees (existing data instances, paginators, finished collection items) get no rules at all.

Behavioral contract, mirroring `src/Resolvers/DataValidationRulesResolver.php` (v4) transposed to the payload driven walk:

* A data object or collectable property whose value is optional and absent, or nullable and null, gets top level rules only (no recursion). This is also the recursion terminator for self referencing data classes, exactly as in v4.
* A data object property gets top level rules (`ArrayType` plus inference) and then recursion. Recursion happens even when the value is absent or not an array (with an empty child payload), so nested `required` errors match v4 (`'author.name' => required` when `author` is missing entirely).
* A finished nested instance (`$value instanceof $property->type->dataClass`) gets no rules, no recursion. ValidateAction re-injects it (Task 7).
* A collectable property gets top level rules `Present` plus `ArrayType` plus inference. Then, when the value is an array, per item: a finished item (instance of the item class) gets nothing; a non array item gets `['array']` at its concrete index path; an array item recurses with the item's class (`indexClasses[$index] ?? child node class ?? $property->type->dataClass`) and the shared child node.
* A paginator value (`AbstractPaginator` or `AbstractCursorPaginator`) gets no rules at all, not even top level ones; the whole property is re-injected after validation.
* Recursion passes the child structure node from `$node['children'][$property->name]` (null when Fill never entered), and the child class comes from the child node when it exists (morph resolution result), otherwise `$property->type->dataClass`.

- [ ] **Step 1: Write the failing tests (append to the existing file)**

```php
it('generates nested rules for a data object property', function () {
    $rules = generateRules(NestedData::class, ['simple' => ['string' => 'Hello']]);

    expect($rules->rules)->toEqual([
        'simple' => ['required', 'array'],
        'simple.string' => ['required', 'string'],
    ]);
});

it('generates nested required rules when the nested payload is missing entirely', function () {
    $rules = generateRules(NestedData::class, []);

    expect($rules->rules)->toEqual([
        'simple' => ['required', 'array'],
        'simple.string' => ['required', 'string'],
    ]);
});

it('generates top level rules only for a nullable data object that is null', function () {
    $dataClass = new class () extends Data {
        public ?SimpleData $simple;
    };

    $rules = generateRules($dataClass::class, ['simple' => null]);

    expect($rules->rules)->toEqual([
        'simple' => ['nullable', 'array'],
    ]);
});

it('generates no rules for a finished nested data instance', function () {
    $rules = generateRules(NestedData::class, ['simple' => SimpleData::from('Hello')]);

    expect($rules->rules)->toBe([]);
});

it('generates concrete per index rules for collections', function () {
    $rules = generateRules(rulesCollectionDataClass(), ['items' => [['string' => 'a'], ['string' => 'b']]]);

    expect($rules->rules)->toEqual([
        'items' => ['present', 'array'],
        'items.0.string' => ['required', 'string'],
        'items.1.string' => ['required', 'string'],
    ]);
});

it('marks non array collection items with an array rule at their index', function () {
    $rules = generateRules(rulesCollectionDataClass(), ['items' => [['string' => 'a'], 'garbage']]);

    expect($rules->rules['items.1'])->toEqual(['array'])
        ->and($rules->rules)->toHaveKey('items.0.string');
});

it('generates no rules for finished collection items but keeps rules for the rest', function () {
    $rules = generateRules(rulesCollectionDataClass(), ['items' => [SimpleData::from('a'), ['string' => 'b']]]);

    expect($rules->rules)->not->toHaveKey('items.0.string')
        ->and($rules->rules)->toHaveKey('items.1.string');
});

it('generates no rules for a paginator value', function () {
    $items = new \Illuminate\Pagination\LengthAwarePaginator([['string' => 'a']], 1, 15);

    $rules = generateRules(rulesCollectionDataClass(), ['items' => $items]);

    expect($rules->rules)->toBe([]);
});

it('uses the mapped original keys inside nested collections', function () {
    $dataClass = new class () extends Data {
        /** @var array<int, SimpleDataWithMappedProperty> */
        public array $items;
    };

    $rules = generateRules($dataClass::class, ['items' => [['description' => 'a']]]);

    expect($rules->rules)->toHaveKey('items.0.description');
});

it('validates morphable collection items against their resolved concrete classes', function () {
    $dataClass = new class () extends Data {
        /** @var array<int, AbstractPropertyMorphableData> */
        public array $items;
    };

    $rules = generateRules($dataClass::class, ['items' => [
        ['variant' => 'a', 'a' => 'foo'],
        ['variant' => 'b', 'b' => true],
    ]]);

    expect($rules->rules)->toHaveKey('items.0.a')
        ->and($rules->rules)->toHaveKey('items.1.b')
        ->and($rules->rules)->not->toHaveKey('items.0.b');
});

it('emits an EnsurePropertyMorphable rule that fails when the morph never resolved', function () {
    $rules = generateRules(AbstractPropertyMorphableData::class, ['variant' => 'non-existing']);

    $variantRules = $rules->rules['variant'];
    $morphRule = collect($variantRules)->first(
        fn (mixed $rule) => $rule instanceof \Spatie\LaravelData\Support\Validation\EnsurePropertyMorphable
    );

    expect($morphRule)->not->toBeNull();

    $failed = false;
    $morphRule->validate('variant', 'non-existing', function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeTrue();
});

it('records the per item mapping collision limitation for divergent morphable item classes', function () {
    // Mappings are stored per structure node and shared across collection items.
    // When two item classes map the same property name to different input keys,
    // the last filled item wins. This pins the known limitation from spec section 8.
    $stateStructure = v5CreationState(rulesCollectionDataClass(), [
        ['items' => [['string' => 'a']]],
    ])->structure();

    expect($stateStructure['children']['items']['mappings'])->toBe([]);
})->todo('Replace with a real divergent morphable fake once one exists; see spec section 8.');
```

Imports to add at the top of the test file: `NestedData`, `SimpleData`, `AbstractPropertyMorphableData` from `Spatie\LaravelData\Tests\Fakes`. Define the `rulesCollectionDataClass()` helper locally in this test file (Pest helper functions are global but only defined once their file loads, so do not depend on `FillActionTest.php`):

```php
function rulesCollectionDataClass(): string
{
    $dataClass = new class () extends Data {
        /** @var array<int, SimpleData> */
        public array $items;
    };

    return $dataClass::class;
}
```

Check `tests/Fakes/AbstractPropertyMorphableData.php` for the real discriminator property name and the concrete classes' properties (`PropertyMorphableDataA` has `a`, `PropertyMorphableDataB` has `b` in the plan 2 tests); adjust key names in the tests to the actual fakes rather than inventing new fakes.

- [ ] **Step 2: Run the tests to verify the new ones fail**

Run: `pest tests/Support/Creation/Actions/GenerateRulesActionTest.php --compact`
Expected: the Task 4 tests still pass, the new ones FAIL (nested keys missing).

- [ ] **Step 3: Implement**

Extend `generateForNode`'s property loop. After the `hasDefaultValue` skip and before the scalar fallthrough, insert the two branches:

```php
            if ($property->type->kind->isDataObject()) {
                $this->generateForDataObjectProperty(
                    $state,
                    $dataClass,
                    $property,
                    $node,
                    $value,
                    $hasValue,
                    $propertyPath,
                    $path,
                    $generated,
                );

                continue;
            }

            if ($property->type->kind->isDataCollectable()) {
                $this->generateForCollectionProperty(
                    $state,
                    $dataClass,
                    $property,
                    $node,
                    $value,
                    $hasValue,
                    $propertyPath,
                    $path,
                    $generated,
                );

                continue;
            }
```

The two new methods:

```php
    protected function generateForDataObjectProperty(
        ConstructionState $state,
        DataClass $dataClass,
        DataProperty $property,
        ?array $node,
        mixed $value,
        bool $hasValue,
        ValidationPath $propertyPath,
        ValidationPath $path,
        GeneratedRules $generated,
    ): void {
        /** @var class-string $targetClass */
        $targetClass = $property->type->dataClass;

        if ($hasValue && $value instanceof $targetClass) {
            return;
        }

        $generated->rules[$propertyPath->get()] = $this->generatePropertyRules(
            $state,
            $dataClass,
            $property,
            $value,
            $path,
            baseRules: [new ArrayType()],
        );

        if ($property->type->isOptional && ! $hasValue) {
            return;
        }

        if ($property->type->isNullable && $value === null) {
            return;
        }

        $childNode = $node['children'][$property->name] ?? null;

        $this->generateForNode(
            $state,
            $this->dataConfig->getDataClass($childNode['class'] ?? $targetClass),
            $childNode,
            is_array($value) ? $value : [],
            $propertyPath,
            $generated,
        );
    }

    protected function generateForCollectionProperty(
        ConstructionState $state,
        DataClass $dataClass,
        DataProperty $property,
        ?array $node,
        mixed $value,
        bool $hasValue,
        ValidationPath $propertyPath,
        ValidationPath $path,
        GeneratedRules $generated,
    ): void {
        if ($value instanceof AbstractPaginator || $value instanceof AbstractCursorPaginator) {
            return;
        }

        /** @var class-string $itemBaseClass */
        $itemBaseClass = $property->type->dataClass;

        $isEmptyOptional = $property->type->isOptional && ! $hasValue;
        $isEmptyNullable = $property->type->isNullable && $value === null;

        $generated->rules[$propertyPath->get()] = $this->generatePropertyRules(
            $state,
            $dataClass,
            $property,
            $value,
            $path,
            baseRules: ($isEmptyOptional || $isEmptyNullable)
                ? [new ArrayType()]
                : [new Present(), new ArrayType()],
        );

        if ($isEmptyOptional || $isEmptyNullable) {
            return;
        }

        if (! is_array($value)) {
            return;
        }

        $childNode = $node['children'][$property->name] ?? null;

        foreach ($value as $index => $item) {
            $itemPath = $propertyPath->property((string) $index);

            if (is_object($item) && $item instanceof $itemBaseClass) {
                continue;
            }

            if (! is_array($item)) {
                $generated->rules[$itemPath->get()] = ['array'];

                continue;
            }

            $itemClass = $childNode['indexClasses'][$index] ?? $childNode['class'] ?? $itemBaseClass;

            $this->generateForNode(
                $state,
                $this->dataConfig->getDataClass($itemClass),
                $childNode,
                $item,
                $itemPath,
                $generated,
            );
        }
    }
```

Add the imports `Illuminate\Pagination\AbstractPaginator` and `Illuminate\Pagination\AbstractCursorPaginator`.

Two notes. First, v4 gave `Present` to collections but not to data objects; keep that asymmetry (the tests encode it). Second, the optional or nullable empty container gets `ArrayType` without `Present`, matching v4's `isDataRelated` early branch. `Present` in `$baseRules` suppresses the inferred `required` through the `shouldInferRequired` check that already exists.

- [ ] **Step 4: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/Actions/GenerateRulesActionTest.php --compact`
Expected: PASS (plus the one todo).

- [ ] **Step 5: Run the full suite and commit**

Run: `pest --compact`, expect no failures. Then:

```bash
git add src/Support/Creation/Actions/GenerateRulesAction.php tests/Support/Creation/Actions/GenerateRulesActionTest.php
git commit -m "Generate nested and per index collection rules"
```

---

### Task 6: rules(), messages(), attributes() overrides, key translation, and the rule hooks

**Files:**
- Modify: `src/Support/Creation/Actions/GenerateRulesAction.php`
- Test: `tests/Support/Creation/Actions/GenerateRulesActionTest.php` (append)

**Interfaces:**
- Consumes: Tasks 4 and 5.
- Produces: per node evaluation of `rules()` overrides with a `ValidationContext` (per collection item, which is the `distinct` fix), `MergeValidationRules` support, property name to original key translation for `rules()`, `messages()`, and `attributes()`, and execution of the `beforeRules` and `afterRules` hooks.

Contract details:

* `rules()` overrides are written in property name space (spec section 10, breaking change). Keys are translated segment wise through the structure: a segment that matches a mapping is replaced by its original key, `*` and numeric segments pass through, unknown segments stay as they are.
* Overrides are evaluated per node when `DataClass::$hasDynamicValidationRules` is true, with a `ValidationContext($nodePayload, $fullPayload, $nodePath)`. Because the walk visits every collection item as its own node, an override that inspects its payload sees the item payload, replacing v4's `Rule::forEach` machinery.
* An override for a property replaces the generated rules for it; with the `MergeValidationRules` class attribute it merges instead (v4 parity).
* The default skip for absent properties with defaults does NOT apply when `rules()` provides rules for that property (#1187, spec section 9).
* `beforeRules` hooks run per property before inference; the first hook returning non null supplies the rules (denormalized through `RuleDenormalizer`), and inference is skipped. `afterRules` hooks always run, chained, on the final denormalized array.
* `messages()` and `attributes()` are collected per node, translated the same way. A message key without a dot whose value is a string gets the v4 wildcard prefix treatment (`$path->property("*.{$key}")`). Child node entries win over parent entries (v4 parity), which falls out of writing child entries first and using `??=` for parents. Memoize the `app()->call` results per class name in the action to avoid re-invoking `messages()` for every collection item.

- [ ] **Step 1: Write the failing tests (append)**

```php
it('applies rules overrides keyed by property name and translated to the original key', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('description')]
        public string $mapped;

        public static function rules(): array
        {
            return [
                'mapped' => ['required', 'string', 'min:5'],
            ];
        }
    };

    $rules = generateRules($dataClass::class, ['description' => 'Hello']);

    expect($rules->rules['description'])->toEqual(['required', 'string', 'min:5'])
        ->and($rules->rules)->not->toHaveKey('mapped');
});

it('applies rules overrides even when a defaulted property is absent', function () {
    $dataClass = new class () extends Data {
        public string $name = 'default';

        public static function rules(): array
        {
            return ['name' => ['string', 'min:2']];
        }
    };

    $rules = generateRules($dataClass::class, []);

    expect($rules->rules['name'])->toEqual(['string', 'min:2']);
});

it('merges override rules when MergeValidationRules is present', function () {
    $dataClass = new #[MergeValidationRules] class () extends Data {
        public string $name;

        public static function rules(): array
        {
            return ['name' => ['min:5']];
        }
    };

    $rules = generateRules($dataClass::class, ['name' => 'Hello']);

    expect($rules->rules['name'])->toEqual(['required', 'string', 'min:5']);
});

it('evaluates rules overrides per collection item with the item payload in context', function () {
    $dataClass = new class () extends Data {
        /** @var array<int, DataWithPayloadDependentRules> */
        public array $items;
    };

    $rules = generateRules($dataClass::class, ['items' => [
        ['type' => 'strict', 'value' => 'x'],
        ['type' => 'loose', 'value' => 'y'],
    ]]);

    expect($rules->rules['items.0.value'])->toContain('min:10')
        ->and($rules->rules['items.1.value'])->not->toContain('min:10');
});

it('lets a beforeRules hook take over rule generation for a property', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->beforeRulesHook(function ($property, $path, $value) {
            return $property->name === 'string' ? ['string', 'max:2'] : null;
        })
        ->get();

    $state = v5CreationState(SimpleData::class, [['string' => 'Hello']], $context);

    $rules = generateRulesAction()->execute($state);

    expect($rules->rules['string'])->toEqual(['string', 'max:2']);
});

it('runs afterRules hooks on every generated property rule set', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->afterRulesHook(fn (array $rules, $property, $path, $value) => [...$rules, 'max:10'])
        ->get();

    $state = v5CreationState(SimpleData::class, [['string' => 'Hello']], $context);

    $rules = generateRulesAction()->execute($state);

    expect($rules->rules['string'])->toEqual(['required', 'string', 'max:10']);
});

it('collects messages and attributes translated to original keys', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('description')]
        public string $mapped;

        public static function messages(): array
        {
            return ['mapped.required' => 'We need this.'];
        }

        public static function attributes(): array
        {
            return ['mapped' => 'the description'];
        }
    };

    $generated = generateRules($dataClass::class, ['description' => 'Hello']);

    expect($generated->messages)->toBe(['description.required' => 'We need this.'])
        ->and($generated->attributes)->toBe(['description' => 'the description']);
});

it('collects nested messages under the nested path', function () {
    $generated = generateRules(NestedData::class, ['simple' => ['string' => 'Hello']]);

    // NestedData has no messages, this asserts nothing crashes and stays empty
    expect($generated->messages)->toBe([]);
});
```

Add the imports `MapInputName` (`Spatie\LaravelData\Attributes\MapInputName`) and `MergeValidationRules` (`Spatie\LaravelData\Attributes\MergeValidationRules`). Create the fake `tests/Fakes/DataWithPayloadDependentRules.php`:

```php
<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class DataWithPayloadDependentRules extends Data
{
    public string $type;

    public string $value;

    public static function rules(ValidationContext $context): array
    {
        if (($context->payload['type'] ?? null) === 'strict') {
            return ['value' => ['required', 'string', 'min:10']];
        }

        return [];
    }
}
```

- [ ] **Step 2: Run the tests to verify the new ones fail**

Run: `pest tests/Support/Creation/Actions/GenerateRulesActionTest.php --compact`
Expected: new tests FAIL.

- [ ] **Step 3: Implement**

In `generateForNode`, resolve overrides at the top, thread them through the default skip, apply them at the bottom, then collect messages:

```php
    protected function generateForNode(...): void
    {
        $overwritten = $this->resolveOverwrittenRules($state, $dataClass, $nodePayload, $path);

        foreach ($dataClass->properties as $property) {
            // existing skip:
            if ($property->validate === false) {
                continue;
            }

            // ... originalKey, propertyPath, hasValue, value unchanged ...

            if ($property->hasDefaultValue && ! $hasValue && ! array_key_exists($property->name, $overwritten)) {
                continue;
            }

            // ... existing object / collectable / scalar branches unchanged ...
        }

        $this->applyOverwrittenRules($state, $dataClass, $overwritten, $node, $path, $generated);
        $this->collectMessagesAndAttributes($dataClass, $node, $path, $generated);
    }
```

New methods and the hook wiring:

```php
    /** @return array<string, mixed> */
    protected function resolveOverwrittenRules(
        ConstructionState $state,
        DataClass $dataClass,
        mixed $nodePayload,
        ValidationPath $path,
    ): array {
        if (! $dataClass->hasDynamicValidationRules) {
            return [];
        }

        $validationContext = new ValidationContext(
            is_array($nodePayload) ? $nodePayload : [],
            $state->payload(),
            $path
        );

        return app()->call([$dataClass->name, 'rules'], ['context' => $validationContext]);
    }

    protected function applyOverwrittenRules(
        ConstructionState $state,
        DataClass $dataClass,
        array $overwritten,
        ?array $node,
        ValidationPath $path,
        GeneratedRules $generated,
    ): void {
        $shouldMerge = $dataClass->attributes->has(MergeValidationRules::class);

        foreach ($overwritten as $key => $rules) {
            $target = $path->property($this->translateKey($key, $node))->get();

            $denormalized = collect(Arr::wrap($rules))
                ->map(fn (mixed $rule) => $this->ruleDenormalizer->execute($rule, $path))
                ->flatten()
                ->all();

            $generated->rules[$target] = $shouldMerge
                ? array_merge($generated->rules[$target] ?? [], $denormalized)
                : $denormalized;
        }
    }

    protected function translateKey(string $key, ?array $node): string
    {
        $translated = [];
        $current = $node;

        foreach (explode('.', $key) as $segment) {
            if ($segment === '*' || is_numeric($segment)) {
                $translated[] = $segment;

                continue;
            }

            if ($current === null) {
                $translated[] = $segment;

                continue;
            }

            $translated[] = $current['mappings'][$segment] ?? $segment;
            $current = $current['children'][$segment] ?? null;
        }

        return implode('.', $translated);
    }

    protected function collectMessagesAndAttributes(
        DataClass $dataClass,
        ?array $node,
        ValidationPath $path,
        GeneratedRules $generated,
    ): void {
        foreach ($this->resolveClassMessages($dataClass) as $key => $message) {
            $messageKey = ! str_contains($key, '.') && is_string($message)
                ? $path->property('*.'.$this->translateKey($key, $node))->get()
                : $path->property($this->translateKey($key, $node))->get();

            $generated->messages[$messageKey] ??= $message;
        }

        foreach ($this->resolveClassAttributes($dataClass) as $key => $attribute) {
            $generated->attributes[$path->property($this->translateKey($key, $node))->get()] ??= $attribute;
        }
    }

    protected function resolveClassMessages(DataClass $dataClass): array
    {
        if (! method_exists($dataClass->name, 'messages')) {
            return [];
        }

        return $this->resolvedMessages[$dataClass->name] ??= app()->call([$dataClass->name, 'messages']);
    }

    protected function resolveClassAttributes(DataClass $dataClass): array
    {
        if (! method_exists($dataClass->name, 'attributes')) {
            return [];
        }

        return $this->resolvedAttributes[$dataClass->name] ??= app()->call([$dataClass->name, 'attributes']);
    }
```

Add the properties `protected array $resolvedMessages = [];` and `protected array $resolvedAttributes = [];` and the imports `Illuminate\Support\Arr`, `Spatie\LaravelData\Attributes\MergeValidationRules`, `Spatie\LaravelData\Support\Validation\ValidationContext`.

Ordering matters here: messages and attributes are collected AFTER the property loop, and children recursed inside the loop already wrote theirs, so `??=` gives child entries precedence over parent entries, which is the v4 behavior. Keep that ordering.

Hook wiring in `generatePropertyRules`: the hooks need the property path, which the callers already compute, so extend the signature with it. Final signature:

```php
    protected function generatePropertyRules(
        ConstructionState $state,
        DataClass $dataClass,
        DataProperty $property,
        mixed $value,
        ValidationPath $nodePath,
        ValidationPath $propertyPath,
        array $baseRules = [],
    ): array {
        foreach ($state->creationContext->beforeRulesHooks as $hook) {
            $overridden = $hook($property, $propertyPath, $value);

            if ($overridden !== null) {
                return $this->runAfterRulesHooks(
                    $this->ruleDenormalizer->execute($overridden, $nodePath),
                    $state,
                    $property,
                    $propertyPath,
                    $value,
                );
            }
        }

        // ... existing body unchanged ...

        return $this->runAfterRulesHooks(
            $this->ruleDenormalizer->execute($rules->all(), $nodePath),
            $state,
            $property,
            $propertyPath,
            $value,
        );
    }

    protected function runAfterRulesHooks(
        array $rules,
        ConstructionState $state,
        DataProperty $property,
        ValidationPath $propertyPath,
        mixed $value,
    ): array {
        foreach ($state->creationContext->afterRulesHooks as $hook) {
            $rules = $hook($rules, $property, $propertyPath, $value);
        }

        return $rules;
    }
```

Update the three call sites (scalar branch, data object branch, collection branch) to pass `$propertyPath`.

- [ ] **Step 4: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/Actions/GenerateRulesActionTest.php --compact`
Expected: PASS.

- [ ] **Step 5: Run the full suite and commit**

Run: `pest --compact`, expect no failures. Then:

```bash
git add src/Support/Creation/Actions/GenerateRulesAction.php tests/Support/Creation/Actions/GenerateRulesActionTest.php tests/Fakes/DataWithPayloadDependentRules.php
git commit -m "Apply rule overrides, messages, attributes, and rule hooks"
```

---

### Task 7: ValidateAction with validated payload extraction and re-injection

**Files:**
- Modify: `src/Support/Creation/ConstructionState.php` (add `replacePayload`)
- Create: `src/Support/Creation/ValidationResult.php`
- Create: `src/Support/Creation/Actions/ValidateAction.php`
- Test: `tests/Support/Creation/Actions/ValidateActionTest.php`
- Test: `tests/Support/Creation/ConstructionStateTest.php` (append one test)

**Interfaces:**
- Produces `ConstructionState::replacePayload(array $payload): void`. Replaces the whole payload tree; only valid at the root (path depth 0). Do not add guards, a docblock line `Only call at the root of the tree.` suffices.
- Produces `ValidationResult`: readonly value object, `public readonly array $validated` (the post afterValidation hook result of `$validator->validated()`), `public readonly bool $precognitive` (always false in this task, Task 8 sets it).
- Produces `ValidateAction::__construct(DataConfig $dataConfig, GenerateRulesAction $generateRulesAction)` with `execute(ConstructionState $state, array $payloads): ValidationResult`. `$payloads` is the raw `from()` argument list (needed for precognition in Task 8; pass `[]` in tests that do not care). Mutates the state payload on success. Throws Laravel's `ValidationException` on failure, decorated with `redirect`, `redirectRoute`, and `errorBag` from the data class when those static methods exist (copy the logic from `src/Resolvers/ValidatedPayloadResolver.php`, which stays untouched for v4).
- Execution order inside `execute`: beforeValidation hooks (chained over the full payload, result written back with `replacePayload`), rule generation, validator construction (`ValidatorFacade::make($state->payload(), $rules, $messages, $attributes)`), `stopOnFirstFailure` when the data class defines it, withValidator hooks, `$validator->validate()`, `$validator->validated()`, afterValidation hooks (chained), re-injection, `replacePayload`, return.
- Re-injection: values that got no rules are missing from `validated()` and must be copied back from the Fill payload (spec section 10). Recursive walk over structure, Fill payload, and validated payload in parallel. Copy from Fill into validated when the validated array does not have the original key and the Fill payload does, and one of: the property has `validate === false`; the Fill value is an instance of the property's data class (finished nested object); the Fill value is a paginator. Inside collections, copy finished items into their index. Recurse into arrays present on both sides. Properties skipped because they were absent with a default get nothing here; `ResolveAbsencesAction` handles them.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Support/Creation/ConstructionStateTest.php`:

```php
it('can replace the whole payload at the root', function () {
    $state = ConstructionState::create(
        CreationContextFactory::createFromConfig(SimpleData::class)->get(),
        SimpleData::class
    );

    $state->writeValue('string', 'old');
    $state->replacePayload(['string' => 'new']);

    expect($state->payload())->toBe(['string' => 'new']);
});
```

`tests/Support/Creation/Actions/ValidateActionTest.php`:

```php
<?php

use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\Actions\GenerateRulesAction;
use Spatie\LaravelData\Support\Creation\Actions\ValidateAction;
use Spatie\LaravelData\Support\Creation\AutoNullResolver;
use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Validation\RuleDenormalizer;
use Spatie\LaravelData\Support\Validation\RuleNormalizer;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

function validateAction(): ValidateAction
{
    return new ValidateAction(
        app(DataConfig::class),
        new GenerateRulesAction(
            app(DataConfig::class),
            app(RuleNormalizer::class),
            app(RuleDenormalizer::class),
            new AutoNullResolver(app(DataConfig::class)),
        ),
    );
}

function validateState(string $dataClass, array $payload, ?CreationContext $context = null): ConstructionState
{
    return v5CreationState($dataClass, [$payload], $context);
}

it('passes a valid payload and keeps it as the state payload', function () {
    $state = validateState(SimpleData::class, ['string' => 'Hello']);

    $result = validateAction()->execute($state, []);

    expect($result->validated)->toBe(['string' => 'Hello'])
        ->and($state->payload())->toBe(['string' => 'Hello'])
        ->and($result->precognitive)->toBeFalse();
});

it('throws a validation exception with original key error keys', function () {
    $state = validateState(SimpleDataWithMappedProperty::class, []);

    try {
        validateAction()->execute($state, []);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('description');

        return;
    }

    $this->fail('No validation exception thrown');
});

it('validates a payload sent under the unmapped property name under that name', function () {
    $state = validateState(SimpleDataWithMappedProperty::class, ['string' => 12345]);

    try {
        validateAction()->execute($state, []);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('string');

        return;
    }

    $this->fail('No validation exception thrown');
});

it('drops keys that no rules covered from the payload', function () {
    $state = validateState(SimpleData::class, ['string' => 'Hello', 'unknown' => 'x']);

    validateAction()->execute($state, []);

    expect($state->payload())->toBe(['string' => 'Hello']);
});

it('removes values excluded by exclude rules', function () {
    $dataClass = new class () extends Data {
        public string $name;

        public ?string $optionalNote;

        public static function rules(): array
        {
            return ['optionalNote' => ['exclude_if:name,secret', 'nullable', 'string']];
        }
    };

    $state = validateState($dataClass::class, ['name' => 'secret', 'optionalNote' => 'drop me']);

    validateAction()->execute($state, []);

    expect($state->payload())->toBe(['name' => 'secret']);
});

it('re-injects properties excluded from validation', function () {
    $dataClass = new class () extends Data {
        public string $name;

        #[WithoutValidation]
        public string $internal;
    };

    $state = validateState($dataClass::class, ['name' => 'Hello', 'internal' => 'kept']);

    validateAction()->execute($state, []);

    expect($state->payload())->toBe(['name' => 'Hello', 'internal' => 'kept']);
});

it('re-injects a finished nested data instance', function () {
    $simple = SimpleData::from('Hello');

    $state = validateState(NestedData::class, ['simple' => $simple]);

    validateAction()->execute($state, []);

    expect($state->payload()['simple'])->toBe($simple);
});

it('re-injects finished collection items at their index', function () {
    $finished = SimpleData::from('a');

    $state = validateState(fillCollectionTestDataClass(), ['items' => [$finished, ['string' => 'b']]]);

    validateAction()->execute($state, []);

    expect($state->payload()['items'][0])->toBe($finished)
        ->and($state->payload()['items'][1])->toBe(['string' => 'b']);
});

it('re-injects a paginator value untouched', function () {
    $paginator = new \Illuminate\Pagination\LengthAwarePaginator([['string' => 'a']], 1, 15);

    $state = validateState(fillCollectionTestDataClass(), ['items' => $paginator]);

    validateAction()->execute($state, []);

    expect($state->payload()['items'])->toBe($paginator);
});

it('runs beforeValidation, withValidator, and afterValidation hooks in order', function () {
    $order = [];

    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->beforeValidationHook(function (array $payload) use (&$order) {
            $order[] = 'beforeValidation';
            $payload['string'] = 'replaced';

            return $payload;
        })
        ->withValidatorHook(function ($validator) use (&$order) {
            $order[] = 'withValidator';
        })
        ->afterValidationHook(function (array $validated) use (&$order) {
            $order[] = 'afterValidation';
            $validated['string'] = strtoupper($validated['string']);

            return $validated;
        })
        ->get();

    $state = validateState(SimpleData::class, ['string' => 'Hello'], $context);

    $result = validateAction()->execute($state, []);

    expect($order)->toBe(['beforeValidation', 'withValidator', 'afterValidation'])
        ->and($result->validated)->toBe(['string' => 'REPLACED'])
        ->and($state->payload())->toBe(['string' => 'REPLACED']);
});
```

`fillCollectionTestDataClass()`: a file local helper returning the class string of an anonymous data class with `/** @var array<int, SimpleData> */ public array $items;` (same shape as `rulesCollectionDataClass()` from the Task 5 test file, but with its own name because Pest helper functions share one global namespace).

- [ ] **Step 2: Run the tests to verify they fail**

Run: `pest tests/Support/Creation/Actions/ValidateActionTest.php --compact`
Expected: FAIL, `ValidateAction` not found.

- [ ] **Step 3: Implement**

`ConstructionState::replacePayload`, placed after `payload()`:

```php
    /**
     * Only call at the root of the tree.
     */
    public function replacePayload(array $payload): void
    {
        $this->payload = $payload;
    }
```

`src/Support/Creation/ValidationResult.php`:

```php
<?php

namespace Spatie\LaravelData\Support\Creation;

class ValidationResult
{
    public function __construct(
        public readonly array $validated,
        public readonly bool $precognitive = false,
    ) {
    }
}
```

`src/Support/Creation/Actions/ValidateAction.php`:

```php
<?php

namespace Spatie\LaravelData\Support\Creation\Actions;

use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\ValidationResult;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class ValidateAction
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected GenerateRulesAction $generateRulesAction,
    ) {
    }

    /**
     * @param array<int, mixed> $payloads
     */
    public function execute(ConstructionState $state, array $payloads): ValidationResult
    {
        $creationContext = $state->creationContext;

        foreach ($creationContext->beforeValidationHooks as $hook) {
            $state->replacePayload($hook($state->payload()));
        }

        $generated = $this->generateRulesAction->execute($state);

        $dataClass = $this->dataConfig->getDataClass($state->structure()['class']);

        $validator = ValidatorFacade::make(
            $state->payload(),
            $generated->rules,
            $generated->messages,
            $generated->attributes
        );

        if (method_exists($dataClass->name, 'stopOnFirstFailure')) {
            $validator->stopOnFirstFailure(app()->call([$dataClass->name, 'stopOnFirstFailure']));
        }

        foreach ($creationContext->withValidatorHooks as $hook) {
            $hook($validator);
        }

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            throw $this->decorateException($dataClass, $exception);
        }

        $validated = $validator->validated();

        foreach ($creationContext->afterValidationHooks as $hook) {
            $validated = $hook($validated);
        }

        $state->replacePayload($this->reinjectUnvalidatedValues(
            $dataClass,
            $state->structure(),
            $state->payload(),
            $validated,
        ));

        return new ValidationResult($validated);
    }

    protected function decorateException(
        DataClass $dataClass,
        ValidationException $exception
    ): ValidationException {
        if (method_exists($dataClass->name, 'redirect')) {
            $exception->redirectTo(app()->call([$dataClass->name, 'redirect']));
        }

        if (method_exists($dataClass->name, 'redirectRoute')) {
            $exception->redirectTo(route(app()->call([$dataClass->name, 'redirectRoute'])));
        }

        if (method_exists($dataClass->name, 'errorBag')) {
            $exception->errorBag(app()->call([$dataClass->name, 'errorBag']));
        }

        return $exception;
    }

    /**
     * @param ?array{class: ?string, mappings: array<string, string>, children: array<string, array>} $node
     */
    protected function reinjectUnvalidatedValues(
        DataClass $dataClass,
        ?array $node,
        array $fillPayload,
        array $validated,
    ): array {
        foreach ($dataClass->properties as $property) {
            $originalKey = $node['mappings'][$property->name] ?? $property->name;

            if (! array_key_exists($originalKey, $fillPayload)) {
                continue;
            }

            $fillValue = $fillPayload[$originalKey];

            if (array_key_exists($originalKey, $validated)) {
                $validated[$originalKey] = $this->reinjectIntoValue(
                    $property,
                    $node['children'][$property->name] ?? null,
                    $fillValue,
                    $validated[$originalKey],
                );

                continue;
            }

            if ($this->shouldReinject($property, $fillValue)) {
                $validated[$originalKey] = $fillValue;
            }
        }

        return $validated;
    }

    protected function shouldReinject(DataProperty $property, mixed $fillValue): bool
    {
        if ($property->validate === false) {
            return true;
        }

        if ($fillValue instanceof AbstractPaginator || $fillValue instanceof AbstractCursorPaginator) {
            return true;
        }

        $targetClass = $property->type->dataClass;

        if ($targetClass !== null && $fillValue instanceof $targetClass) {
            return true;
        }

        return false;
    }

    protected function reinjectIntoValue(
        DataProperty $property,
        ?array $childNode,
        mixed $fillValue,
        mixed $validatedValue,
    ): mixed {
        if (! is_array($fillValue) || ! is_array($validatedValue)) {
            return $validatedValue;
        }

        if ($property->type->kind->isDataObject()) {
            $childClass = $childNode['class'] ?? $property->type->dataClass;

            return $this->reinjectUnvalidatedValues(
                $this->dataConfig->getDataClass($childClass),
                $childNode,
                $fillValue,
                $validatedValue,
            );
        }

        if ($property->type->kind->isDataCollectable()) {
            /** @var class-string $itemBaseClass */
            $itemBaseClass = $property->type->dataClass;

            foreach ($fillValue as $index => $item) {
                if (! array_key_exists($index, $validatedValue) && is_object($item) && $item instanceof $itemBaseClass) {
                    $validatedValue[$index] = $item;

                    continue;
                }

                if (array_key_exists($index, $validatedValue) && is_array($item) && is_array($validatedValue[$index])) {
                    $itemClass = $childNode['indexClasses'][$index] ?? $childNode['class'] ?? $itemBaseClass;

                    $validatedValue[$index] = $this->reinjectUnvalidatedValues(
                        $this->dataConfig->getDataClass($itemClass),
                        $childNode,
                        $item,
                        $validatedValue[$index],
                    );
                }
            }

            ksort($validatedValue);

            return $validatedValue;
        }

        return $validatedValue;
    }
}
```

The `ksort` keeps collection indices ordered after re-inserting finished items between validated ones. `$property->type->dataClass` is null for non data related properties, hence the null guard in `shouldReinject`.

- [ ] **Step 4: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/Actions/ValidateActionTest.php tests/Support/Creation/ConstructionStateTest.php --compact`
Expected: PASS.

- [ ] **Step 5: Run the full suite and commit**

Run: `pest --compact`, expect no failures. Then:

```bash
git add src/Support/Creation/ConstructionState.php src/Support/Creation/ValidationResult.php src/Support/Creation/Actions/ValidateAction.php tests/Support/Creation/Actions/ValidateActionTest.php tests/Support/Creation/ConstructionStateTest.php
git commit -m "Validate the construction state and re-inject unvalidated values"
```

---

### Task 8: Precognition support in ValidateAction

**Files:**
- Modify: `src/Support/Creation/Actions/ValidateAction.php`
- Test: `tests/Support/Creation/Actions/ValidateActionTest.php` (append)

**Interfaces:**
- Consumes: Task 7's `ValidateAction`.
- Produces: when a `Request` among `$payloads` reports `isPrecognitive()`, the generated rules are filtered with `$request->filterPrecognitiveRules($rules)` before the validator is built, the validator runs, and the action returns `ValidationResult` with `precognitive: true` WITHOUT touching the state payload and WITHOUT running afterValidation hooks or re-injection. The flow (plan 4) stops after validation when the result is precognitive; no object is built (spec section 10).

Laravel machinery (verified in this repo's vendor): `isPrecognitive()` and `filterPrecognitiveRules()` are macros on `Request`, driven by the `Precognition` and `Precognition-Validate-Only` headers plus the `precognitive` request attribute set by Laravel's middleware. In tests, mark a request precognitive with `$request->attributes->set('precognitive', true)` and set the header `Precognition-Validate-Only` to a comma separated key list.

- [ ] **Step 1: Write the failing tests (append)**

```php
it('filters rules to the precognitive keys and stops before touching the payload', function () {
    $dataClass = new class () extends Data {
        public string $name;

        public string $email;
    };

    $request = \Illuminate\Http\Request::create('/example', 'POST', ['name' => 'Hello']);
    $request->attributes->set('precognitive', true);
    $request->headers->set('Precognition-Validate-Only', 'name');

    $state = validateState($dataClass::class, ['name' => 'Hello']);
    $payloadBefore = $state->payload();

    $result = validateAction()->execute($state, [$request]);

    expect($result->precognitive)->toBeTrue()
        ->and($state->payload())->toBe($payloadBefore);
});

it('fails precognitive validation for a filtered key that is invalid', function () {
    $dataClass = new class () extends Data {
        public string $name;

        public string $email;
    };

    $request = \Illuminate\Http\Request::create('/example', 'POST');
    $request->attributes->set('precognitive', true);
    $request->headers->set('Precognition-Validate-Only', 'name');

    $state = validateState($dataClass::class, []);

    validateAction()->execute($state, [$request]);
})->throws(ValidationException::class);
```

Note for the first test: `email` is missing from the payload, so without filtering the run would throw; passing proves the filter removed the `email` rules.

- [ ] **Step 2: Run the tests to verify they fail**

Run: `pest tests/Support/Creation/Actions/ValidateActionTest.php --compact`
Expected: the first new test FAILS with a `ValidationException` for `email`.

- [ ] **Step 3: Implement**

In `ValidateAction::execute`, after rule generation and before building the validator:

```php
        $request = $this->findPrecognitiveRequest($payloads);
        $rules = $generated->rules;

        if ($request !== null) {
            $rules = $request->filterPrecognitiveRules($rules);
        }
```

Use `$rules` in `ValidatorFacade::make`. After `$validator->validate()` succeeds, exit early for precognition, before `validated()`, the afterValidation hooks, and re-injection:

```php
        if ($request !== null) {
            return new ValidationResult(validated: [], precognitive: true);
        }
```

The helper:

```php
    protected function findPrecognitiveRequest(array $payloads): ?Request
    {
        foreach ($payloads as $payload) {
            if ($payload instanceof Request && $payload->isPrecognitive()) {
                return $payload;
            }
        }

        return null;
    }
```

Import `Illuminate\Http\Request`.

- [ ] **Step 4: Run the tests, the full suite, and commit**

Run: `pest tests/Support/Creation/Actions/ValidateActionTest.php --compact`, then `pest --compact`.
Expected: PASS everywhere. Then:

```bash
git add src/Support/Creation/Actions/ValidateAction.php tests/Support/Creation/Actions/ValidateActionTest.php
git commit -m "Filter rules for precognitive requests during validation"
```

---

### Task 9: ResolveAbsencesAction

**Files:**
- Create: `src/Support/Creation/Actions/ResolveAbsencesAction.php`
- Test: `tests/Support/Creation/Actions/ResolveAbsencesActionTest.php`

**Interfaces:**
- Consumes: `ConstructionState` navigation (`enterProperty`, `enterItem`, `leave`, `hasValue`, `getValue`, `writeValue`, `originalKey`, `nodeClass`), `AutoNullResolver` from Task 1.
- Produces `ResolveAbsencesAction::__construct(DataConfig $dataConfig, AutoNullResolver $autoNullResolver)` with `execute(ConstructionState $state): void`, mutating the payload. Per property at every node, when the original key has no value: write the PHP default when there is one; else write `Optional::create()` when the type is optional and the context uses optional values; else write null when the type is nullable and auto null applies; else leave it missing (Instantiate throws later, plan 4). Recurse into nested data objects and collection items whose payload value is an array; skip finished instances and paginators (never enter non arrays). Spec sections 5 (step 7) and 9.
- IMPORTANT: only call `enterProperty`/`enterItem` on values that are already arrays, because entering materializes the payload node and would turn "left missing" into an empty array.

- [ ] **Step 1: Write the failing tests**

```php
<?php

use Spatie\LaravelData\Attributes\WithoutAutoNull;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\Actions\ResolveAbsencesAction;
use Spatie\LaravelData\Support\Creation\AutoNullResolver;
use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\NestedAbsencesTestData;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

function resolveAbsencesAction(): ResolveAbsencesAction
{
    return new ResolveAbsencesAction(
        app(DataConfig::class),
        new AutoNullResolver(app(DataConfig::class)),
    );
}

function absencesState(string $dataClass, array $payload, ?CreationContext $context = null): ConstructionState
{
    return v5CreationState($dataClass, [$payload], $context);
}

it('applies php defaults for absent values', function () {
    $dataClass = new class () extends Data {
        public string $name = 'default';
    };

    $state = absencesState($dataClass::class, []);

    resolveAbsencesAction()->execute($state);

    expect($state->payload())->toBe(['name' => 'default']);
});

it('does not overwrite present values with defaults', function () {
    $dataClass = new class () extends Data {
        public string $name = 'default';
    };

    $state = absencesState($dataClass::class, ['name' => 'given']);

    resolveAbsencesAction()->execute($state);

    expect($state->payload())->toBe(['name' => 'given']);
});

it('fills optional properties with Optional', function () {
    $dataClass = new class () extends Data {
        public string|Optional $name;
    };

    $state = absencesState($dataClass::class, []);

    resolveAbsencesAction()->execute($state);

    expect($state->payload()['name'])->toBeInstanceOf(Optional::class);
});

it('leaves optional properties missing when optional values are disabled', function () {
    $dataClass = new class () extends Data {
        public string|Optional $name;
    };

    $context = CreationContextFactory::createFromConfig($dataClass::class)
        ->withoutOptionalValues()
        ->get();

    $state = absencesState($dataClass::class, [], $context);

    resolveAbsencesAction()->execute($state);

    expect($state->payload())->toBe([]);
});

it('auto nulls absent nullable properties by default', function () {
    $dataClass = new class () extends Data {
        public ?string $name;
    };

    $state = absencesState($dataClass::class, []);

    resolveAbsencesAction()->execute($state);

    expect($state->payload())->toBe(['name' => null]);
});

it('leaves absent nullable properties missing in strict mode', function () {
    $dataClass = new class () extends Data {
        #[WithoutAutoNull]
        public ?string $name;
    };

    $state = absencesState($dataClass::class, []);

    resolveAbsencesAction()->execute($state);

    expect($state->payload())->toBe([]);
});

it('prefers the default over Optional and null', function () {
    $dataClass = new class () extends Data {
        public string|Optional|null $name = 'default';
    };

    $state = absencesState($dataClass::class, []);

    resolveAbsencesAction()->execute($state);

    expect($state->payload())->toBe(['name' => 'default']);
});

it('prefers Optional over null', function () {
    $dataClass = new class () extends Data {
        public string|Optional|null $name;
    };

    $state = absencesState($dataClass::class, []);

    resolveAbsencesAction()->execute($state);

    expect($state->payload()['name'])->toBeInstanceOf(Optional::class);
});

it('resolves absences inside nested data objects', function () {
    $dataClass = new class () extends Data {
        public NestedAbsencesTestData $nested;
    };

    $state = absencesState($dataClass::class, ['nested' => ['name' => 'x']]);

    resolveAbsencesAction()->execute($state);

    expect($state->payload()['nested'])->toBe(['name' => 'x', 'note' => null]);
});

it('resolves absences inside collection items', function () {
    $dataClass = new class () extends Data {
        /** @var array<int, NestedAbsencesTestData> */
        public array $items;
    };

    $state = absencesState($dataClass::class, ['items' => [['name' => 'a'], ['name' => 'b', 'note' => 'x']]]);

    resolveAbsencesAction()->execute($state);

    expect($state->payload()['items'])->toBe([
        ['name' => 'a', 'note' => null],
        ['name' => 'b', 'note' => 'x'],
    ]);
});

it('skips finished nested instances and leaves absent nested objects to their own rules', function () {
    $simple = SimpleData::from('Hello');

    $state = absencesState(NestedData::class, ['simple' => $simple]);

    resolveAbsencesAction()->execute($state);

    expect($state->payload()['simple'])->toBe($simple);
});

it('writes absent values under the mapped original key', function () {
    $state = absencesState(SimpleDataWithMappedProperty::class, []);

    // string is required, absent, no default, not nullable: left missing
    resolveAbsencesAction()->execute($state);

    expect($state->payload())->toBe([]);
});
```

Create the fake `tests/Fakes/NestedAbsencesTestData.php`:

```php
<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class NestedAbsencesTestData extends Data
{
    public string $name;

    public ?string $note;
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `pest tests/Support/Creation/Actions/ResolveAbsencesActionTest.php --compact`
Expected: FAIL, class not found.

- [ ] **Step 3: Implement**

```php
<?php

namespace Spatie\LaravelData\Support\Creation\Actions;

use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\AutoNullResolver;
use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;

class ResolveAbsencesAction
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected AutoNullResolver $autoNullResolver,
    ) {
    }

    public function execute(ConstructionState $state): void
    {
        $this->resolveNode(
            $state,
            $this->dataConfig->getDataClass($state->structure()['class'])
        );
    }

    protected function resolveNode(ConstructionState $state, DataClass $dataClass): void
    {
        foreach ($dataClass->properties as $property) {
            $originalKey = $state->originalKey($property->name);

            if (! $state->hasValue($originalKey)) {
                $this->resolveAbsentValue($state, $dataClass, $property, $originalKey);

                continue;
            }

            if (! is_array($state->getValue($originalKey))) {
                continue;
            }

            if ($property->type->kind->isDataObject()) {
                $state->enterProperty($property->name, $originalKey);

                $this->resolveChildNode($state);

                $state->leave();

                continue;
            }

            if ($property->type->kind->isDataCollectable()) {
                $indices = array_keys($state->getValue($originalKey));

                $state->enterProperty($property->name, $originalKey);

                foreach ($indices as $index) {
                    if (! is_array($state->getValue($index))) {
                        continue;
                    }

                    $state->enterItem($index);

                    $this->resolveChildNode($state);

                    $state->leave();
                }

                $state->leave();
            }
        }
    }

    protected function resolveChildNode(ConstructionState $state): void
    {
        $childClass = $state->nodeClass();

        if ($childClass === null) {
            return;
        }

        $this->resolveNode($state, $this->dataConfig->getDataClass($childClass));
    }

    protected function resolveAbsentValue(
        ConstructionState $state,
        DataClass $dataClass,
        \Spatie\LaravelData\Support\DataProperty $property,
        string|int $originalKey,
    ): void {
        if ($property->hasDefaultValue) {
            $state->writeValue($originalKey, $property->defaultValue);

            return;
        }

        if ($property->type->isOptional && $state->creationContext->useOptionalValues) {
            $state->writeValue($originalKey, Optional::create());

            return;
        }

        if ($property->type->isNullable && $this->autoNullResolver->execute($property, $dataClass)) {
            $state->writeValue($originalKey, null);
        }
    }
}
```

(Import `DataProperty` properly instead of the inline FQCN; the snippet shows it inline only to be unambiguous.)

Note the collection branch reads `array_keys` at the parent level BEFORE `enterProperty`, because `getValue` resolves keys relative to the current path. Inside the entered property, `getValue($index)` then reads the individual items.

- [ ] **Step 4: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/Actions/ResolveAbsencesActionTest.php --compact`
Expected: PASS.

- [ ] **Step 5: Run the full suite and commit**

Run: `pest --compact`, expect no failures. Then:

```bash
git add src/Support/Creation/Actions/ResolveAbsencesAction.php tests/Support/Creation/Actions/ResolveAbsencesActionTest.php tests/Fakes/NestedAbsencesTestData.php
git commit -m "Resolve absent values with defaults, Optional, and auto null"
```

---

### Task 10: Carried regression tests and spec sync

**Files:**
- Modify: `tests/Support/Creation/PrepareDataHookTest.php` (append)
- Modify: `docs/superpowers/specs/2026-08-28-data-v5-creation-design.md`

**Interfaces:** none new. This task closes the plan.

- [ ] **Step 1: Add the zero payload prepareData regression test**

Carried from plan 2: `from()` with no arguments must still fire the prepareData hooks with an empty list the hook may fill. Append to `tests/Support/Creation/PrepareDataHookTest.php`, following that file's existing helper style:

```php
it('fires prepareData hooks with an empty list when from receives no payloads', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareDataHook(function (array $normalized, string $class, string $path, array $payloads) {
            expect($normalized)->toBe([])
                ->and($payloads)->toBe([]);

            return [['string' => 'from hook']];
        })
        ->get();

    $state = v5CreationState(SimpleData::class, [], $context);

    expect($state->payload())->toBe(['string' => 'from hook']);
});
```

Check first whether `FillAction::execute` still turns an empty `$payloads` into a single empty payload (the plan 2 behavior was `[[]]`); after the review pass the hook receives the empty list itself. If the test fails because `execute` never reaches `fillNode` with zero payloads, fix `FillAction` so the hook fires (the empty list must reach the hooks; the spec section 11 wording is authoritative: "an empty list it may fill").

- [ ] **Step 2: Run the test**

Run: `pest tests/Support/Creation/PrepareDataHookTest.php --compact`
Expected: PASS (possibly after the small FillAction fix).

- [ ] **Step 3: Sync the spec**

Update `docs/superpowers/specs/2026-08-28-data-v5-creation-design.md`:

* Section 8 and 5: record that an unresolvable morph no longer throws during Fill; the node keeps the abstract class, validation reports it through `EnsurePropertyMorphable`, and Instantiate throws `CannotCreateAbstractClass` when validation did not run.
* Section 9: name the concrete pieces: config key `auto_null` (default true), attributes `AutoNull` and `WithoutAutoNull` (property beats class beats config).
* Section 11: pin the hook signatures as implemented: `beforeValidation(array $payload): array`, `beforeRules(DataProperty $property, ValidationPath $path, mixed $value): ?array`, `afterRules(array $rules, DataProperty $property, ValidationPath $path, mixed $value): array` (receives denormalized Laravel rules; afterRules also runs when beforeRules supplied the rules), `withValidator(Validator $validator): void`, `afterValidation(array $validated): array`.
* Section 13: add the `auto_null` config key to the not breaking list framing (new key, default preserves v4 behavior).

Remember the documentation rule: no dashes as punctuation in the spec text.

- [ ] **Step 4: Run the full suite and phpstan**

Run: `pest --compact` and `vendor/bin/phpstan analyse`.
Expected: suite green, phpstan at the 7 pre-existing errors.

- [ ] **Step 5: Commit**

```bash
git add tests/Support/Creation/PrepareDataHookTest.php src/Support/Creation/Actions/FillAction.php docs/superpowers/specs/2026-08-28-data-v5-creation-design.md
git commit -m "Add carried regression tests and sync the spec for validation"
```

(Drop `src/Support/Creation/Actions/FillAction.php` from the add when no fix was needed.)

---

## Self review notes (already applied)

* Spec coverage: section 9 (Tasks 1, 4, 9), section 10 rule action and key spaces (Tasks 4, 5, 6), collections without `Rule::forEach` (Task 5, per item overrides Task 6), validated payload and re-injection (Task 7), entry point hooks (Task 2, execution Tasks 6, 7), precognition (Task 8), section 11 hooks 2 through 6 (Tasks 2, 6, 7). Hooks 7 and 8 (`beforeCreation`, `afterCreation`) belong to plan 4's Instantiate. `Data::validate()` and `getValidationRules()` entry points are plan 4 wiring.
* The `distinct` acceptance case: concrete per index rules are expected to fix it. If a Task 5 or 6 test with `Distinct` shows Laravel's `distinct` rule misbehaving under concrete keys, report it as a blocker instead of switching to wildcard keys silently.
* Type consistency check: `GeneratedRules` (Tasks 4, 6, 7), `PropertyRuleSet` (Task 4), `ValidationResult` (Tasks 7, 8), `AutoNullResolver::execute(DataProperty, DataClass): bool` (Tasks 1, 4, 9), hook property names (Tasks 2, 6, 7) are used with identical spellings throughout.
