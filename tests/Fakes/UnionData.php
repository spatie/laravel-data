<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;

class UnionData extends Data
{
    public function __construct(
        public int $id,
        public SimpleData|Lazy $simple,
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection|Lazy $dataCollection,
        public FakeModel|Lazy $fakeModel,
    ) {
    }

    public static function fromInt(int $id): self
    {
        return new self(
            id: $id,
            simple: Lazy::create(fn () => SimpleData::from('A')),
            dataCollection: Lazy::create(fn () => SimpleData::collection(['B', 'C'])),
            fakeModel: Lazy::create(fn () => FakeModel::factory()->create([
                'string' => 'lazy',
            ])),
        );
    }

    public static function fromString(string $name): self
    {
        return new self(
            id: 1,
            simple: SimpleData::from($name),
            dataCollection: SimpleData::collection(['B', 'C']),
            fakeModel: FakeModel::factory()->create([
                'string' => 'non-lazy',
            ]),
        );
    }
}
