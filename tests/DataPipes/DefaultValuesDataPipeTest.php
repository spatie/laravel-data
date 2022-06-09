<?php

namespace Spatie\LaravelData\Tests\DataPipes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Tests\TestCase;

class DefaultValuesDataPipeTest extends TestCase
{
    /** @test */
    public function it_can_create_a_data_object_with_defaults_empty()
    {
        $dataClass = new class ('', '', '') extends Data {
            public function __construct(
                public ?string $string,
                public Optional|string $optionalString,
                public string $stringWithDefault = 'Hi',
            ) {
            }
        };

        $this->assertEquals(
            new $dataClass(null, new Optional(), 'Hi'),
            $dataClass::from([])
        );
    }
}
