<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;
use Spatie\LaravelData\Tests\TestCase;

class DataValidatorResolverTest extends TestCase
{
    private DataValidatorResolver $resolver;

    public function setUp() : void
    {
        parent::setUp();

        $this->resolver = app(DataValidatorResolver::class);
    }

    /** @test */
    public function it_can_set_the_validator_to_stop_on_the_first_failure()
    {
        $dataClass = new class extends Data{
            public static function stopOnFirstFailure(): bool
            {
                return true;
            }
        };

        $validator = $this->resolver->execute($dataClass::class, []);

        $this->assertTrue(invade($validator)->stopOnFirstFailure);
    }
}
