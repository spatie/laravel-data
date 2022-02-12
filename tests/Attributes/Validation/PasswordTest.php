<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Generator;
use Illuminate\Validation\Rules\Password as ValidationPassword;
use ReflectionClass;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Tests\TestCase;

class PasswordTest extends TestCase
{
    /**
     * @dataProvider preconfiguredPasswordValidationsProvider
     */
    public function testPasswordRuleReturnsPreconfiguredPasswordValidations(callable $setDefaults, array $expectedConfig): void
    {
        ValidationPassword::$defaultCallback = null;
        $setDefaults();

        [$rule] = (new Password(default: true))->getRules();
        $clazz = new ReflectionClass($rule);

        foreach ($expectedConfig as $key => $expected) {
            $prop = $clazz->getProperty($key);
            $actual = $prop->getValue($rule);

            $this->assertSame($expected, $actual);
        }
    }

    public function preconfiguredPasswordValidationsProvider(): Generator
    {
        yield 'min length set to 42' => [
            'setDefaults' => fn () => ValidationPassword::defaults(fn () => ValidationPassword::min(42)),
            'expectedConfig' => [
                'min' => 42,
            ],
        ];

        yield 'unconfigured' => [
            'setDefaults' => fn () => null,
            'expectedConfig' => [
                'min' => 8,
            ],
        ];

        yield 'uncompromised' => [
            'setDefaults' => fn() => ValidationPassword::defaults(fn () => ValidationPassword::min(69)->uncompromised(7)),
            'expectedConfig' => [
                'min' => 69,
                'uncompromised' => true,
                'compromisedThreshold' => 7,
            ],
        ];
    }
}
