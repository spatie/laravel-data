<?php

namespace Spatie\LaravelData\Tests\Attributes\Validation;

use Carbon\CarbonImmutable;
use DateTimeZone;
use Generator;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\ValidationAttribute;
use Spatie\LaravelData\Tests\Fakes\DummyBackedEnum;
use Spatie\LaravelData\Tests\TestCase;

class ValidationAttributeTest extends TestCase
{
    /** @test */
    public function it_can_get_a_string_representation_of_rules()
    {
        $rule = new StringType();

        $this->assertEquals('string', (string) $rule);
    }

    /**
     * @test
     * @dataProvider valuesDataProvider
     */
    public function it_can_normalize_values(mixed $input, mixed $output)
    {
        $normalizer = new class () extends ValidationAttribute {
            public function execute(mixed $value): mixed
            {
                return $this->normalizeValue($value);
            }

            public function getRules(): array
            {
                return [];
            }

            public static function create(string ...$parameters): static
            {
                return new self();
            }

            public static function keyword(): string
            {
                return '';
            }
        };

        $this->assertEquals($output, $normalizer->execute($input));
    }

    public function valuesDataProvider(): Generator
    {
        yield [
            'input' => 'Hello world',
            'output' => 'Hello world',
        ];

        yield [
            'input' => 42,
            'output' => '42',
        ];

        yield [
            'input' => 3.14,
            'output' => '3.14',
        ];

        yield [
            'input' => true,
            'output' => 'true',
        ];

        yield [
            'input' => false,
            'output' => 'false',
        ];

        yield [
            'input' => ['a', 'b', 'c'],
            'output' => 'a,b,c',
        ];

        yield [
            'input' => CarbonImmutable::create(2020, 05, 16, 0, 0, 0, new DateTimeZone('Europe/Brussels')),
            'output' => '2020-05-16T00:00:00+02:00',
        ];

        yield [
            'input' => DummyBackedEnum::FOO,
            'output' => 'foo',
        ];

        yield [
            'input' => [DummyBackedEnum::FOO, DummyBackedEnum::BOO],
            'output' => 'foo,boo',
        ];
    }
}
