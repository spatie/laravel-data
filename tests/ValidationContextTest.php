<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation as Attributes;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataValidationRulesResolver;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataContainer;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\ValidationContextManager;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Support\Validation\ValidationRule;

beforeEach(function () {
    app(ValidationContextManager::class)->clearContext();
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();
});

afterEach(function () {
    app(ValidationContextManager::class)->clearContext();
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();
});

it('registers ValidationContextManager as a singleton', function () {
    $manager1 = app(ValidationContextManager::class);
    $manager2 = app(ValidationContextManager::class);

    expect($manager1)->toBe($manager2);
});

it('can set and get context', function () {
    $manager = app(ValidationContextManager::class);

    expect($manager->getContext())->toBeNull();

    $manager->setContext('store');
    expect($manager->getContext())->toBe('store');

    $manager->setContext('update');
    expect($manager->getContext())->toBe('update');
});

it('can clear context', function () {
    $manager = app(ValidationContextManager::class);

    $manager->setContext('store');
    expect($manager->getContext())->toBe('store');

    $manager->clearContext();
    expect($manager->getContext())->toBeNull();
});

it('applies rules without context to all contexts', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;
    };

    // Without context
    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        [],
        ValidationPath::create(),
        DataRules::create(),
    );
    expect($rules['name'])->toContain('required');
    expect($rules['name'])->toContain('string');

    // Reset for next assertion
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();

    // With store context
    app(ValidationContextManager::class)->setContext('store');
    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        [],
        ValidationPath::create(),
        DataRules::create(),
    );
    expect($rules['name'])->toContain('required');
    expect($rules['name'])->toContain('string');
});

it('applies context-specific rules only in matching context', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Required(context: 'store')]
        #[Min(8, context: 'store')]
        public ?string $password = null;
    };

    // Without context - password rules should NOT apply
    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        ['name' => 'John'],
        ValidationPath::create(),
        DataRules::create(),
    );

    expect($rules)->toHaveKey('name');
    expect($rules)->not->toHaveKey('password');

    // Reset caches
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();

    // With store context - password rules SHOULD apply
    app(ValidationContextManager::class)->setContext('store');

    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        ['name' => 'John'],
        ValidationPath::create(),
        DataRules::create(),
    );

    expect($rules)->toHaveKey('name');
    expect($rules)->toHaveKey('password');
    expect($rules['password'])->toContain('required');
    expect($rules['password'])->toContain('min:8');
});

it('does not apply context-specific rules in non-matching context', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Required(context: 'store')]
        public ?string $password = null;
    };

    // With update context - password required rule should NOT apply
    app(ValidationContextManager::class)->setContext('update');

    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        ['name' => 'John'],
        ValidationPath::create(),
        DataRules::create(),
    );

    expect($rules)->toHaveKey('name');
    expect($rules)->not->toHaveKey('password');
});

it('validates required field in store context', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Required(context: 'store')]
        public ?string $password = null;
    };

    app(ValidationContextManager::class)->setContext('store');

    $exception = null;
    try {
        $dataClass::validate(['name' => 'John']);
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->errors())->toHaveKey('password');
});

it('does not require field in update context when required only in store', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Required(context: 'store')]
        public ?string $password = null;
    };

    app(ValidationContextManager::class)->setContext('update');

    $validated = $dataClass::validate(['name' => 'John']);

    expect($validated['name'])->toBe('John');
});

it('validates min length in store context', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Required(context: 'store')]
        #[Min(8, context: 'store')]
        public ?string $password = null;
    };

    app(ValidationContextManager::class)->setContext('store');

    $exception = null;
    try {
        $dataClass::validate(['name' => 'John', 'password' => 'short']);
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->errors())->toHaveKey('password');
    expect($exception->errors()['password'][0])->toContain('8 characters');
});

it('does not validate min length in update context when min only applies in store', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Min(8, context: 'store')]
        public ?string $password = null;
    };

    app(ValidationContextManager::class)->setContext('update');

    $validated = $dataClass::validate(['name' => 'John', 'password' => 'short']);

    expect($validated['password'])->toBe('short');
});

it('supports array of contexts', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Required(context: ['store', 'import'])]
        public ?string $password = null;
    };

    // Should apply in store context
    app(ValidationContextManager::class)->setContext('store');
    $exception = null;
    try {
        $dataClass::validate(['name' => 'John']);
    } catch (ValidationException $e) {
        $exception = $e;
    }
    expect($exception)->not->toBeNull();
    expect($exception->errors())->toHaveKey('password');

    // Reset for next test
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();

    // Should apply in import context
    app(ValidationContextManager::class)->setContext('import');
    $exception = null;
    try {
        $dataClass::validate(['name' => 'John']);
    } catch (ValidationException $e) {
        $exception = $e;
    }
    expect($exception)->not->toBeNull();
    expect($exception->errors())->toHaveKey('password');

    // Reset for next test
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();

    // Should NOT apply in update context
    app(ValidationContextManager::class)->setContext('update');
    $validated = $dataClass::validate(['name' => 'John']);
    expect($validated['name'])->toBe('John');
});

it('can use fluent API to set context', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Required(context: 'store')]
        public ?string $password = null;
    };

    // Using fluent API with store context
    $exception = null;
    try {
        $dataClass::withContext('store')->alwaysValidate()->from(['name' => 'John']);
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->errors())->toHaveKey('password');
});

it('can use fluent API without context', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Required(context: 'store')]
        public ?string $password = null;
    };

    // Using fluent API without context - should not require password
    $data = $dataClass::withContext(null)->alwaysValidate()->from(['name' => 'John']);

    expect($data->name)->toBe('John');
    expect($data->password)->toBeNull();
});

it('appliesToContext returns true when no context is defined on rule', function () {
    $rule = new class () extends ValidationRule {
        public function __construct()
        {
            $this->context = null;
        }
    };

    expect($rule->appliesToContext(null))->toBeTrue();
    expect($rule->appliesToContext('store'))->toBeTrue();
    expect($rule->appliesToContext('update'))->toBeTrue();
});

it('appliesToContext returns false when context is defined but current context is null', function () {
    $rule = new class () extends ValidationRule {
        public function __construct()
        {
            $this->context = 'store';
        }
    };

    expect($rule->appliesToContext(null))->toBeFalse();
});

it('appliesToContext returns true when context matches', function () {
    $rule = new class () extends ValidationRule {
        public function __construct()
        {
            $this->context = 'store';
        }
    };

    expect($rule->appliesToContext('store'))->toBeTrue();
    expect($rule->appliesToContext('update'))->toBeFalse();
});

it('appliesToContext works with array of contexts', function () {
    $rule = new class () extends ValidationRule {
        public function __construct()
        {
            $this->context = ['store', 'import'];
        }
    };

    expect($rule->appliesToContext('store'))->toBeTrue();
    expect($rule->appliesToContext('import'))->toBeTrue();
    expect($rule->appliesToContext('update'))->toBeFalse();
});

it('validation attributes accept context parameter', function () {
    // Test Required
    $required = new Required(context: 'store');
    expect($required->context)->toBe('store');

    // Test Min
    $min = new Min(8, context: 'store');
    expect($min->context)->toBe('store');

    // Test Confirmed
    $confirmed = new Confirmed(context: 'store');
    expect($confirmed->context)->toBe('store');
});

it('restores previous context after fluent API from() call', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;
    };

    // Set initial context
    app(ValidationContextManager::class)->setContext('initial');
    expect(app(ValidationContextManager::class)->getContext())->toBe('initial');

    // Use withContext - should restore 'initial' afterward
    $dataClass::withContext('temporary')->alwaysValidate()->from(['name' => 'John']);

    expect(app(ValidationContextManager::class)->getContext())->toBe('initial');
});

it('clears context after fluent API from() call when no previous context', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;
    };

    // Ensure no context is set
    app(ValidationContextManager::class)->clearContext();
    expect(app(ValidationContextManager::class)->getContext())->toBeNull();

    // Use withContext - should clear context afterward (restore to null)
    $dataClass::withContext('temporary')->alwaysValidate()->from(['name' => 'John']);

    expect(app(ValidationContextManager::class)->getContext())->toBeNull();
});

it('handles multiple sequential withContext calls correctly', function () {
    $dataClassA = new class () extends Data {
        #[Required]
        public string $name;
    };

    $dataClassB = new class () extends Data {
        #[Required]
        public string $title;
    };

    // Set initial context
    app(ValidationContextManager::class)->setContext('original');

    // Create factories with different contexts
    $factoryA = $dataClassA::withContext('context-a')->alwaysValidate();
    $factoryB = $dataClassB::withContext('context-b')->alwaysValidate();

    // Execute them - each should restore to 'original'
    $factoryA->from(['name' => 'John']);
    expect(app(ValidationContextManager::class)->getContext())->toBe('original');

    $factoryB->from(['title' => 'Mr']);
    expect(app(ValidationContextManager::class)->getContext())->toBe('original');
});

// Tests for variadic parameter extraction with context

it('extracts context from variadic ArrayType rule with single key', function () {
    $rule = new Attributes\ArrayType('key1', context: 'store');

    expect($rule->context)->toBe('store');
});

it('extracts context from variadic ArrayType rule with multiple keys', function () {
    $rule = new Attributes\ArrayType('key1', 'key2', 'key3', context: 'update');

    expect($rule->context)->toBe('update');
});

it('extracts context from variadic ArrayType rule with array keys', function () {
    $rule = new Attributes\ArrayType(['key1', 'key2'], context: ['store', 'import']);

    expect($rule->context)->toBe(['store', 'import']);
});

it('extracts context from variadic In rule with single value', function () {
    $rule = new Attributes\In('active', context: 'store');

    expect($rule->context)->toBe('store');
});

it('extracts context from variadic In rule with multiple values', function () {
    $rule = new Attributes\In('active', 'pending', 'completed', context: 'admin');

    expect($rule->context)->toBe('admin');
});

it('extracts context from variadic In rule with array values', function () {
    $rule = new Attributes\In(['active', 'pending'], context: ['store', 'update']);

    expect($rule->context)->toBe(['store', 'update']);
});

it('extracts context from variadic NotIn rule', function () {
    $rule = new Attributes\NotIn('banned', 'deleted', context: 'store');

    expect($rule->context)->toBe('store');
});

it('extracts context from variadic StartsWith rule with single value', function () {
    $rule = new Attributes\StartsWith('prefix_', context: 'store');

    expect($rule->context)->toBe('store');
});

it('extracts context from variadic StartsWith rule with multiple values', function () {
    $rule = new Attributes\StartsWith('Mr.', 'Mrs.', 'Ms.', context: 'formal');

    expect($rule->context)->toBe('formal');
});

it('extracts context from variadic EndsWith rule', function () {
    $rule = new Attributes\EndsWith('.com', '.org', context: 'store');

    expect($rule->context)->toBe('store');
});

it('extracts context from variadic DoesntStartWith rule', function () {
    $rule = new Attributes\DoesntStartWith('test_', 'tmp_', context: 'production');

    expect($rule->context)->toBe('production');
});

it('extracts context from variadic DoesntEndWith rule', function () {
    $rule = new Attributes\DoesntEndWith('.tmp', '.bak', context: 'store');

    expect($rule->context)->toBe('store');
});

it('extracts context from variadic Mimes rule', function () {
    $rule = new Attributes\Mimes('jpg', 'png', 'gif', context: 'upload');

    expect($rule->context)->toBe('upload');
});

it('extracts context from variadic MimeTypes rule', function () {
    $rule = new Attributes\MimeTypes('image/jpeg', 'image/png', context: 'store');

    expect($rule->context)->toBe('store');
});

it('extracts context from variadic Prohibits rule', function () {
    $rule = new Attributes\Prohibits('field1', 'field2', context: 'store');

    expect($rule->context)->toBe('store');
});

it('variadic ArrayType rule without context applies to all contexts', function () {
    $rule = new Attributes\ArrayType('key1', 'key2');

    expect($rule->context)->toBeNull();
    expect($rule->appliesToContext(null))->toBeTrue();
    expect($rule->appliesToContext('store'))->toBeTrue();
    expect($rule->appliesToContext('update'))->toBeTrue();
});

it('variadic In rule without context applies to all contexts', function () {
    $rule = new Attributes\In('a', 'b', 'c');

    expect($rule->context)->toBeNull();
    expect($rule->appliesToContext(null))->toBeTrue();
    expect($rule->appliesToContext('store'))->toBeTrue();
});

it('variadic rule with context only applies in matching context', function () {
    $rule = new Attributes\In('active', 'pending', context: 'store');

    expect($rule->appliesToContext('store'))->toBeTrue();
    expect($rule->appliesToContext('update'))->toBeFalse();
    expect($rule->appliesToContext(null))->toBeFalse();
});

it('variadic rule with array context applies to multiple contexts', function () {
    $rule = new Attributes\StartsWith('prefix_', context: ['store', 'import']);

    expect($rule->appliesToContext('store'))->toBeTrue();
    expect($rule->appliesToContext('import'))->toBeTrue();
    expect($rule->appliesToContext('update'))->toBeFalse();
});

it('validates In rule with context in data class', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Attributes\In('active', 'pending', context: 'store')]
        public ?string $status = null;
    };

    // Without context - In rule should NOT apply
    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        ['name' => 'John', 'status' => 'invalid'],
        ValidationPath::create(),
        DataRules::create(),
    );

    expect($rules)->toHaveKey('name');
    // The status field may still have type-inferred rules (like nullable),
    // but the In rule should not be present
    if (isset($rules['status'])) {
        foreach ($rules['status'] as $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\In) {
                throw new \Exception('In rule should not be present when context does not match');
            }
        }
    }

    // Reset caches
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();

    // With store context - In rule SHOULD apply
    app(ValidationContextManager::class)->setContext('store');

    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        ['name' => 'John', 'status' => 'active'],
        ValidationPath::create(),
        DataRules::create(),
    );

    expect($rules)->toHaveKey('name');
    expect($rules)->toHaveKey('status');
    $hasInRule = false;
    foreach ($rules['status'] as $rule) {
        if ($rule instanceof \Illuminate\Validation\Rules\In) {
            $hasInRule = true;
            break;
        }
    }
    expect($hasInRule)->toBeTrue();
});

it('validates ArrayType rule with context in data class', function () {
    $dataClass = new class () extends Data {
        #[Attributes\ArrayType('name', 'email', context: 'store')]
        public ?array $data = null;
    };

    // Without context - ArrayType rule should NOT apply
    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        ['data' => ['name' => 'John']],
        ValidationPath::create(),
        DataRules::create(),
    );

    // The data field may still have type-inferred rules (like nullable, array),
    // but the ArrayType-specific rule 'array:name,email' should not be present
    if (isset($rules['data'])) {
        expect($rules['data'])->not->toContain('array:name,email');
    }

    // Reset caches
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();

    // With store context - ArrayType rule SHOULD apply
    app(ValidationContextManager::class)->setContext('store');

    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        ['data' => ['name' => 'John']],
        ValidationPath::create(),
        DataRules::create(),
    );

    expect($rules)->toHaveKey('data');
    expect($rules['data'])->toContain('array:name,email');
});

it('validates StartsWith rule with context in data class', function () {
    $dataClass = new class () extends Data {
        #[Required]
        #[Attributes\StartsWith('Mr.', 'Mrs.', 'Ms.', context: 'formal')]
        public string $title;
    };

    // Without context - StartsWith rule should NOT apply, Required should still apply
    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        ['title' => 'Dr.'],
        ValidationPath::create(),
        DataRules::create(),
    );

    expect($rules)->toHaveKey('title');
    expect($rules['title'])->toContain('required');
    expect($rules['title'])->not->toContain('starts_with:Mr.,Mrs.,Ms.');

    // Reset caches
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();

    // With formal context - StartsWith rule SHOULD apply
    app(ValidationContextManager::class)->setContext('formal');

    $rules = app(DataValidationRulesResolver::class)->execute(
        $dataClass::class,
        ['title' => 'Mr. Smith'],
        ValidationPath::create(),
        DataRules::create(),
    );

    expect($rules)->toHaveKey('title');
    expect($rules['title'])->toContain('required');
    expect($rules['title'])->toContain('starts_with:Mr.,Mrs.,Ms.');
});

it('validates In rule fails when value not in allowed list within context', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Attributes\In('active', 'pending', context: 'store')]
        public ?string $status = null;
    };

    app(ValidationContextManager::class)->setContext('store');

    $exception = null;
    try {
        $dataClass::validate(['name' => 'John', 'status' => 'invalid']);
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->errors())->toHaveKey('status');
});

it('validates In rule passes when value is in allowed list within context', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Attributes\In('active', 'pending', context: 'store')]
        public ?string $status = null;
    };

    app(ValidationContextManager::class)->setContext('store');

    $validated = $dataClass::validate(['name' => 'John', 'status' => 'active']);

    expect($validated['name'])->toBe('John');
    expect($validated['status'])->toBe('active');
});

it('validates In rule passes any value when context does not match', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Attributes\In('active', 'pending', context: 'store')]
        public ?string $status = null;
    };

    app(ValidationContextManager::class)->setContext('update');

    // Should pass because the In rule does not apply in update context
    $validated = $dataClass::validate(['name' => 'John', 'status' => 'any_value']);

    expect($validated['name'])->toBe('John');
    expect($validated['status'])->toBe('any_value');
});

it('combines multiple different variadic rules with different contexts', function () {
    $dataClass = new class () extends Data {
        #[Required]
        public string $name;

        #[Attributes\In('active', 'pending', 'archived', context: 'admin')]
        #[Attributes\StartsWith('status_', context: 'import')]
        public ?string $status = null;
    };

    // In admin context - In rule should apply
    app(ValidationContextManager::class)->setContext('admin');

    $exception = null;
    try {
        $dataClass::validate(['name' => 'John', 'status' => 'invalid']);
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->errors())->toHaveKey('status');

    // Reset caches
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();

    // In import context - StartsWith rule should apply
    app(ValidationContextManager::class)->setContext('import');

    $exception = null;
    try {
        $dataClass::validate(['name' => 'John', 'status' => 'active']);
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->errors())->toHaveKey('status');

    // Reset caches
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();
    app(ValidationContextManager::class)->setContext('import');

    // Valid value for import context
    $validated = $dataClass::validate(['name' => 'John', 'status' => 'status_active']);

    expect($validated['status'])->toBe('status_active');
});

it('validates EndsWith rule with context', function () {
    $dataClass = new class () extends Data {
        #[Attributes\EndsWith('.com', '.org', context: 'store')]
        public ?string $domain = null;
    };

    app(ValidationContextManager::class)->setContext('store');

    $exception = null;
    try {
        $dataClass::validate(['domain' => 'example.net']);
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->errors())->toHaveKey('domain');

    // Reset caches
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();
    app(ValidationContextManager::class)->setContext('store');

    // Valid value should pass
    $validated = $dataClass::validate(['domain' => 'example.com']);

    expect($validated['domain'])->toBe('example.com');
});

it('validates NotIn rule with context', function () {
    $dataClass = new class () extends Data {
        #[Attributes\NotIn('banned', 'deleted', context: 'store')]
        public ?string $status = null;
    };

    app(ValidationContextManager::class)->setContext('store');

    $exception = null;
    try {
        $dataClass::validate(['status' => 'banned']);
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($exception->errors())->toHaveKey('status');

    // Reset caches
    app(DataConfig::class)->reset();
    DataContainer::get()->reset();
    app(ValidationContextManager::class)->setContext('store');

    // Valid value should pass
    $validated = $dataClass::validate(['status' => 'active']);

    expect($validated['status'])->toBe('active');
});
