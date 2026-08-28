# v5 Plan 1, Foundations Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the v5 creation engine foundations (`ConstructionState`, `SourceReader`, `SourceResolver`) as pure new code, fully unit tested, wired into nothing.

**Architecture:** One mutable state object carries the payload array (data shaped, source keyed), the structure array (class tree shaped, compact nodes), and the current path through a creation flow. Two small static helpers read properties from sources (plain arrays or `Normalized` objects, with v4 multi-payload merge semantics) and lazily normalize nested values. v4 code is not touched; the existing test suite must stay green.

**Tech Stack:** PHP 8.2+, Laravel, Pest for tests. Reuses the existing `Normalized`, `Normalizer`, `UnknownProperty`, `NormalizedModel`, `Optional`, `DataProperty`, `CreationContext` classes.

**Spec:** docs/superpowers/specs/2026-08-28-data-v5-creation-design.md (sections 4, 6)

## Global Constraints

* All new classes live in `src/Support/Creation/`, namespace `Spatie\LaravelData\Support\Creation`.
* No container access (`app()`, `config()`) inside the new classes. Dependencies arrive as constructor or method arguments.
* Structure nodes contain only scalars and arrays: `['class' => ?string, 'mappings' => array, 'children' => array]`. Nothing else.
* v4 behavior stays untouched. Nothing gets wired into the existing creation flow in this plan.
* Multi-source read semantics must match v4 exactly (see `src/Resolvers/DataFromSomethingResolver.php:167-180`): later payloads override earlier ones, but a null or `Optional` value never overwrites an existing value.
* Code style: PSR-12, typed properties, curly braces always, no `else`, early returns.
* Run tests with `pest <path>` from the repository root. The full suite must pass before every commit.
* Git commits: no Co-Authored-By lines.

---

### Task 1: ConstructionState, payload and path handling

**Files:**
- Create: `src/Support/Creation/ConstructionState.php`
- Test: `tests/Support/Creation/ConstructionStateTest.php`

**Interfaces:**
- Consumes: `Spatie\LaravelData\Support\Creation\CreationContext` (existing), `CreationContextFactory::createFromConfig(string $dataClass)` (existing), `Spatie\LaravelData\Tests\Fakes\SimpleData` (existing fake with constructor `public string $string`).
- Produces: `new ConstructionState(CreationContext $creationContext, string $class)`, `enterProperty(string $name, ?string $sourceKey = null): void`, `enterIndex(string|int $index): void`, `leave(): void`, `depth(): int`, `dotPath(string|int|null $key = null): string`, `writePayload(string|int $key, mixed $value): void`, `hasPayload(string|int $key): bool`, `getPayload(string|int $key): mixed`, `payload(): array`.

- [ ] **Step 1: Write the failing tests**

Create `tests/Support/Creation/ConstructionStateTest.php`:

```php
<?php

use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

function makeConstructionState(): ConstructionState
{
    return new ConstructionState(
        CreationContextFactory::createFromConfig(SimpleData::class)->get(),
        SimpleData::class,
    );
}

it('writes payload values at the root', function () {
    $state = makeConstructionState();

    $state->writePayload('title', 'Hello');

    expect($state->payload())->toBe(['title' => 'Hello']);
});

it('writes payload values for nested properties under their source keys', function () {
    $state = makeConstructionState();

    $state->writePayload('title', 'Hello');
    $state->enterProperty('author', 'writer');
    $state->writePayload('name', 'Ruben');
    $state->leave();

    expect($state->payload())->toBe([
        'title' => 'Hello',
        'writer' => ['name' => 'Ruben'],
    ]);
});

it('writes payload values inside collection indices', function () {
    $state = makeConstructionState();

    $state->enterProperty('posts');
    $state->enterIndex(0);
    $state->writePayload('title', 'First');
    $state->leave();
    $state->enterIndex(1);
    $state->writePayload('title', 'Second');
    $state->leave();
    $state->leave();

    expect($state->payload())->toBe([
        'posts' => [
            0 => ['title' => 'First'],
            1 => ['title' => 'Second'],
        ],
    ]);
});

it('reads and checks payload values at the current path', function () {
    $state = makeConstructionState();

    $state->enterProperty('author', 'writer');
    $state->writePayload('name', 'Ruben');

    expect($state->hasPayload('name'))->toBeTrue()
        ->and($state->getPayload('name'))->toBe('Ruben')
        ->and($state->hasPayload('missing'))->toBeFalse()
        ->and($state->getPayload('missing'))->toBeNull();

    $state->leave();

    expect($state->hasPayload('name'))->toBeFalse();
});

it('builds dot paths from payload segments', function () {
    $state = makeConstructionState();

    expect($state->dotPath('title'))->toBe('title');

    $state->enterProperty('author', 'writer');

    expect($state->dotPath())->toBe('writer')
        ->and($state->dotPath('name'))->toBe('writer.name');

    $state->leave();
    $state->enterProperty('posts');
    $state->enterIndex(0);

    expect($state->dotPath('title'))->toBe('posts.0.title')
        ->and($state->depth())->toBe(2);
});
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `pest tests/Support/Creation/ConstructionStateTest.php`
Expected: FAIL with "Class \"Spatie\LaravelData\Support\Creation\ConstructionState\" not found"

- [ ] **Step 3: Write the implementation**

Create `src/Support/Creation/ConstructionState.php`:

```php
<?php

namespace Spatie\LaravelData\Support\Creation;

class ConstructionState
{
    protected array $payload = [];

    protected array $structure;

    /** @var array<int, array{payloadKey: string|int, structureKey: ?string, isIndex: bool}> */
    protected array $path = [];

    public function __construct(
        public readonly CreationContext $creationContext,
        string $class,
    ) {
        $this->structure = static::newNode($class);
    }

    public function enterProperty(string $name, ?string $sourceKey = null): void
    {
        $this->path[] = [
            'payloadKey' => $sourceKey ?? $name,
            'structureKey' => $name,
            'isIndex' => false,
        ];
    }

    public function enterIndex(string|int $index): void
    {
        $this->path[] = [
            'payloadKey' => $index,
            'structureKey' => null,
            'isIndex' => true,
        ];
    }

    public function leave(): void
    {
        array_pop($this->path);
    }

    public function depth(): int
    {
        return count($this->path);
    }

    public function dotPath(string|int|null $key = null): string
    {
        $segments = array_map(
            fn (array $segment) => $segment['payloadKey'],
            $this->path
        );

        if ($key !== null) {
            $segments[] = $key;
        }

        return implode('.', $segments);
    }

    public function writePayload(string|int $key, mixed $value): void
    {
        $slot = &$this->payloadSlot();

        $slot[$key] = $value;
    }

    public function hasPayload(string|int $key): bool
    {
        $slot = $this->currentPayload();

        return is_array($slot) && array_key_exists($key, $slot);
    }

    public function getPayload(string|int $key): mixed
    {
        $slot = $this->currentPayload();

        if (! is_array($slot) || ! array_key_exists($key, $slot)) {
            return null;
        }

        return $slot[$key];
    }

    public function payload(): array
    {
        return $this->payload;
    }

    protected function currentPayload(): mixed
    {
        $slot = $this->payload;

        foreach ($this->path as $segment) {
            $key = $segment['payloadKey'];

            if (! is_array($slot) || ! array_key_exists($key, $slot)) {
                return null;
            }

            $slot = $slot[$key];
        }

        return $slot;
    }

    protected function &payloadSlot(): array
    {
        $slot = &$this->payload;

        foreach ($this->path as $segment) {
            $key = $segment['payloadKey'];

            if (! array_key_exists($key, $slot) || ! is_array($slot[$key])) {
                $slot[$key] = [];
            }

            $slot = &$slot[$key];
        }

        return $slot;
    }

    protected static function newNode(?string $class): array
    {
        return [
            'class' => $class,
            'mappings' => [],
            'children' => [],
        ];
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/ConstructionStateTest.php`
Expected: PASS (5 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Support/Creation/ConstructionState.php tests/Support/Creation/ConstructionStateTest.php
git commit -m "Add ConstructionState payload and path handling"
```

---

### Task 2: ConstructionState, structure operations

**Files:**
- Modify: `src/Support/Creation/ConstructionState.php`
- Test: `tests/Support/Creation/ConstructionStateTest.php` (append)

**Interfaces:**
- Consumes: Task 1's `ConstructionState` (path handling, `newNode()` shape).
- Produces: `recordMapping(string $property, string $sourceKey): void`, `sourceKey(string $property): string`, `setNodeClass(string $class): void`, `nodeClass(): ?string`, `structure(): array`. Structure node shape used by all later plans: `['class' => ?string, 'mappings' => array<string, string>, 'children' => array<string, node>]`, index segments create no nodes.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Support/Creation/ConstructionStateTest.php`:

```php
it('records mappings on the current structure node', function () {
    $state = makeConstructionState();

    $state->recordMapping('author', 'writer');

    expect($state->structure())->toBe([
        'class' => SimpleData::class,
        'mappings' => ['author' => 'writer'],
        'children' => [],
    ]);
});

it('resolves source keys through mappings, defaulting to the property name', function () {
    $state = makeConstructionState();

    $state->recordMapping('author', 'writer');

    expect($state->sourceKey('author'))->toBe('writer')
        ->and($state->sourceKey('title'))->toBe('title');
});

it('creates one structure node per data property, ignoring collection indices', function () {
    $state = makeConstructionState();

    $state->enterProperty('posts');
    $state->enterIndex(3);
    $state->recordMapping('title', 'post_title');
    $state->leave();
    $state->leave();

    expect($state->structure())->toBe([
        'class' => SimpleData::class,
        'mappings' => [],
        'children' => [
            'posts' => [
                'class' => null,
                'mappings' => ['title' => 'post_title'],
                'children' => [],
            ],
        ],
    ]);
});

it('sets and reads node classes for nested nodes', function () {
    $state = makeConstructionState();

    expect($state->nodeClass())->toBe(SimpleData::class);

    $state->enterProperty('author', 'writer');
    $state->setNodeClass(SimpleData::class);

    expect($state->nodeClass())->toBe(SimpleData::class)
        ->and($state->structure()['children']['author']['class'])->toBe(SimpleData::class);
});
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `pest tests/Support/Creation/ConstructionStateTest.php`
Expected: FAIL with "Call to undefined method ...::recordMapping()"

- [ ] **Step 3: Add the structure methods**

Add to `src/Support/Creation/ConstructionState.php`:

```php
    public function recordMapping(string $property, string $sourceKey): void
    {
        $node = &$this->structureNode();

        $node['mappings'][$property] = $sourceKey;
    }

    public function sourceKey(string $property): string
    {
        $node = &$this->structureNode();

        return $node['mappings'][$property] ?? $property;
    }

    public function setNodeClass(string $class): void
    {
        $node = &$this->structureNode();

        $node['class'] = $class;
    }

    public function nodeClass(): ?string
    {
        $node = &$this->structureNode();

        return $node['class'];
    }

    public function structure(): array
    {
        return $this->structure;
    }

    protected function &structureNode(): array
    {
        $node = &$this->structure;

        foreach ($this->path as $segment) {
            if ($segment['isIndex']) {
                continue;
            }

            $key = $segment['structureKey'];

            if (! array_key_exists($key, $node['children'])) {
                $node['children'][$key] = static::newNode(null);
            }

            $node = &$node['children'][$key];
        }

        return $node;
    }
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/ConstructionStateTest.php`
Expected: PASS (9 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Support/Creation/ConstructionState.php tests/Support/Creation/ConstructionStateTest.php
git commit -m "Add ConstructionState structure operations"
```

---

### Task 3: SourceReader, single source reads

**Files:**
- Create: `src/Support/Creation/SourceReader.php`
- Test: `tests/Support/Creation/SourceReaderTest.php`

**Interfaces:**
- Consumes: `Spatie\LaravelData\Normalizers\Normalized\Normalized` (existing, `getProperty(string $name, DataProperty $dataProperty): mixed`), `Spatie\LaravelData\Normalizers\Normalized\UnknownProperty` (existing singleton sentinel, `UnknownProperty::create()`), `Spatie\LaravelData\Support\DataProperty` (existing), `Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory::property(object $class, string $name)` (existing test helper).
- Produces: `SourceReader::read(array|Normalized $source, string|int $key, DataProperty $property): mixed`. The contract every later plan relies on: a missing key returns the `UnknownProperty` sentinel, never null.

- [ ] **Step 1: Write the failing tests**

Create `tests/Support/Creation/SourceReaderTest.php`:

```php
<?php

use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Support\Creation\SourceReader;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

function sourceReaderProperty(): DataProperty
{
    return FakeDataStructureFactory::property(new SimpleData('hello'), 'string');
}

it('reads present keys from an array source', function () {
    expect(SourceReader::read(['title' => 'Hello'], 'title', sourceReaderProperty()))
        ->toBe('Hello');
});

it('reads a present null from an array source', function () {
    expect(SourceReader::read(['title' => null], 'title', sourceReaderProperty()))
        ->toBeNull();
});

it('returns the UnknownProperty sentinel for missing array keys', function () {
    expect(SourceReader::read([], 'title', sourceReaderProperty()))
        ->toBeInstanceOf(UnknownProperty::class);
});

it('reads properties from a Normalized source', function () {
    $normalized = new class () implements Normalized {
        public function getProperty(string $name, DataProperty $dataProperty): mixed
        {
            if ($name === 'title') {
                return 'Hello';
            }

            return UnknownProperty::create();
        }
    };

    expect(SourceReader::read($normalized, 'title', sourceReaderProperty()))->toBe('Hello')
        ->and(SourceReader::read($normalized, 'missing', sourceReaderProperty()))
        ->toBeInstanceOf(UnknownProperty::class);
});
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `pest tests/Support/Creation/SourceReaderTest.php`
Expected: FAIL with "Class \"Spatie\LaravelData\Support\Creation\SourceReader\" not found"

- [ ] **Step 3: Write the implementation**

Create `src/Support/Creation/SourceReader.php`:

```php
<?php

namespace Spatie\LaravelData\Support\Creation;

use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Support\DataProperty;

class SourceReader
{
    public static function read(
        array|Normalized $source,
        string|int $key,
        DataProperty $property
    ): mixed {
        if ($source instanceof Normalized) {
            return $source->getProperty((string) $key, $property);
        }

        if (! array_key_exists($key, $source)) {
            return UnknownProperty::create();
        }

        return $source[$key];
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/SourceReaderTest.php`
Expected: PASS (4 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Support/Creation/SourceReader.php tests/Support/Creation/SourceReaderTest.php
git commit -m "Add SourceReader for single source property reads"
```

---

### Task 4: SourceReader, multi-source winner pick

**Files:**
- Modify: `src/Support/Creation/SourceReader.php`
- Test: `tests/Support/Creation/SourceReaderTest.php` (append)

**Interfaces:**
- Consumes: Task 3's `SourceReader::read()`, `Spatie\LaravelData\Optional` (existing, `Optional::create()`).
- Produces: `SourceReader::readFromMany(array $sources, string|int $key, DataProperty $property): mixed` where `$sources` is a list of `array|Normalized`. Semantics contract for later plans: identical to v4's merge in `DataFromSomethingResolver::runPipeline()` (later sources override, null and `Optional` never overwrite an existing value, all sources missing the key returns `UnknownProperty`).

- [ ] **Step 1: Write the failing tests**

Append to `tests/Support/Creation/SourceReaderTest.php` (add `use Spatie\LaravelData\Optional;` to the imports):

```php
it('later sources override earlier ones', function () {
    expect(SourceReader::readFromMany(
        [['title' => 'First'], ['title' => 'Second']],
        'title',
        sourceReaderProperty()
    ))->toBe('Second');
});

it('null never overwrites an existing value', function () {
    expect(SourceReader::readFromMany(
        [['title' => 'First'], ['title' => null]],
        'title',
        sourceReaderProperty()
    ))->toBe('First');
});

it('Optional never overwrites an existing value', function () {
    expect(SourceReader::readFromMany(
        [['title' => 'First'], ['title' => Optional::create()]],
        'title',
        sourceReaderProperty()
    ))->toBe('First');
});

it('null wins when it is the only present value', function () {
    expect(SourceReader::readFromMany(
        [[], ['title' => null]],
        'title',
        sourceReaderProperty()
    ))->toBeNull();
});

it('sources missing the key are skipped', function () {
    expect(SourceReader::readFromMany(
        [['title' => 'First'], []],
        'title',
        sourceReaderProperty()
    ))->toBe('First');
});

it('returns the UnknownProperty sentinel when no source has the key', function () {
    expect(SourceReader::readFromMany(
        [[], []],
        'title',
        sourceReaderProperty()
    ))->toBeInstanceOf(UnknownProperty::class);
});
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `pest tests/Support/Creation/SourceReaderTest.php`
Expected: FAIL with "Call to undefined method ...::readFromMany()"

- [ ] **Step 3: Add the implementation**

Add to `src/Support/Creation/SourceReader.php` (add `use Spatie\LaravelData\Optional;` to the imports):

```php
    /**
     * @param array<int, array|Normalized> $sources
     */
    public static function readFromMany(
        array $sources,
        string|int $key,
        DataProperty $property
    ): mixed {
        $result = UnknownProperty::create();

        foreach ($sources as $source) {
            $value = static::read($source, $key, $property);

            if ($value instanceof UnknownProperty) {
                continue;
            }

            if ($result instanceof UnknownProperty) {
                $result = $value;

                continue;
            }

            if ($value === null || $value instanceof Optional) {
                continue;
            }

            $result = $value;
        }

        return $result;
    }
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/SourceReaderTest.php`
Expected: PASS (10 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Support/Creation/SourceReader.php tests/Support/Creation/SourceReaderTest.php
git commit -m "Add multi-source winner pick to SourceReader"
```

---

### Task 5: SourceResolver, lazy per-node normalization

**Files:**
- Create: `src/Support/Creation/SourceResolver.php`
- Test: `tests/Support/Creation/SourceResolverTest.php`

**Interfaces:**
- Consumes: `Spatie\LaravelData\Normalizers\Normalizer` (existing, `normalize(mixed $value): null|array|Normalized`), `Spatie\LaravelData\Normalizers\ModelNormalizer`, `Spatie\LaravelData\Normalizers\JsonNormalizer` (existing, no constructor arguments), `Spatie\LaravelData\Exceptions\CannotCreateData::noNormalizerFound(string $dataClass, mixed $value)` (existing), `Spatie\LaravelData\Normalizers\Normalized\NormalizedModel` (existing), test fakes `FakeModel` and `FakeNestedModel` (existing, `FakeNestedModel::fakeModel()` is a BelongsTo relation), Task 3's `SourceReader::read()`.
- Produces: `SourceResolver::resolve(string $dataClass, mixed $value, array $normalizers): array|Normalized` where `$normalizers` is a list of `Normalizer` instances. Contract: null becomes an empty array, arrays and `Normalized` pass through untouched, anything else runs the chain (first non-null wins), no match throws `CannotCreateData`.

- [ ] **Step 1: Write the failing tests**

Create `tests/Support/Creation/SourceResolverTest.php`:

```php
<?php

use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Normalizers\JsonNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\NormalizedModel;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Support\Creation\SourceReader;
use Spatie\LaravelData\Support\Creation\SourceResolver;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('passes arrays through untouched', function () {
    expect(SourceResolver::resolve(SimpleData::class, ['a' => 1], []))
        ->toBe(['a' => 1]);
});

it('turns null into an empty array', function () {
    expect(SourceResolver::resolve(SimpleData::class, null, []))->toBe([]);
});

it('passes Normalized objects through untouched', function () {
    $normalized = new class () implements Normalized {
        public function getProperty(string $name, DataProperty $dataProperty): mixed
        {
            return UnknownProperty::create();
        }
    };

    expect(SourceResolver::resolve(SimpleData::class, $normalized, []))
        ->toBe($normalized);
});

it('runs the normalizer chain, first non-null wins', function () {
    expect(SourceResolver::resolve(
        SimpleData::class,
        '{"title": "Hello"}',
        [new ModelNormalizer(), new JsonNormalizer()]
    ))->toBe(['title' => 'Hello']);
});

it('throws when no normalizer accepts the value', function () {
    SourceResolver::resolve(SimpleData::class, 42, [new JsonNormalizer()]);
})->throws(CannotCreateData::class);

it('resolves a nested model read into a new NormalizedModel', function () {
    $related = new FakeModel();
    $related->setRawAttributes(['string' => 'Hello']);

    $model = new FakeNestedModel();
    $model->setRelation('fakeModel', $related);

    $relationProperty = FakeDataStructureFactory::property(new class () {
        public ?FakeModel $fakeModel = null;
    }, 'fakeModel');

    $value = SourceReader::read(new NormalizedModel($model), 'fakeModel', $relationProperty);

    expect($value)->toBe($related);

    $childSource = SourceResolver::resolve(SimpleData::class, $value, [new ModelNormalizer()]);

    expect($childSource)->toBeInstanceOf(NormalizedModel::class);

    $stringProperty = FakeDataStructureFactory::property(new class () {
        public ?string $string = null;
    }, 'string');

    expect(SourceReader::read($childSource, 'string', $stringProperty))->toBe('Hello');
});
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `pest tests/Support/Creation/SourceResolverTest.php`
Expected: FAIL with "Class \"Spatie\LaravelData\Support\Creation\SourceResolver\" not found"

- [ ] **Step 3: Write the implementation**

Create `src/Support/Creation/SourceResolver.php`:

```php
<?php

namespace Spatie\LaravelData\Support\Creation;

use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalizer;

class SourceResolver
{
    /**
     * @param array<int, Normalizer> $normalizers
     */
    public static function resolve(
        string $dataClass,
        mixed $value,
        array $normalizers
    ): array|Normalized {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Normalized) {
            return $value;
        }

        foreach ($normalizers as $normalizer) {
            $normalized = $normalizer->normalize($value);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        throw CannotCreateData::noNormalizerFound($dataClass, $value);
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `pest tests/Support/Creation/SourceResolverTest.php`
Expected: PASS (6 tests)

- [ ] **Step 5: Run the full suite to confirm nothing regressed**

Run: `pest`
Expected: PASS, same result as before this plan started.

- [ ] **Step 6: Commit**

```bash
git add src/Support/Creation/SourceResolver.php tests/Support/Creation/SourceResolverTest.php
git commit -m "Add SourceResolver for lazy per-node normalization"
```
