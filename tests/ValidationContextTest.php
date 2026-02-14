<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Validation\ValidationException;
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
