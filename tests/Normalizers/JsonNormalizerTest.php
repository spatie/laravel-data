<?php

namespace Spatie\LaravelData\Tests\Normalizers;

use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Normalizers\JsonNormalizer;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\MultiData;
use Spatie\LaravelData\Tests\TestCase;

class JsonNormalizerTest extends TestCase
{
    /** @test */
    public function it_can_create_a_data_object_from_json()
    {
        $originalData = new MultiData('Hello', 'World');

        $createdData = MultiData::from($originalData->toJson());

        $this->assertEquals($originalData, $createdData);
    }

    /** @test */
    public function it_wont_create_a_data_object_from_a_regular_string()
    {
        $this->expectException(CannotCreateData::class);

        MultiData::from('Hello World');
    }
}
