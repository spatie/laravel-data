<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Orchestra\Testbench\Concerns\CreatesApplication;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Subject;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\MultiNestedData;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use function Amp\Iterator\toArray;

class DataBench
{
    use CreatesApplication;

    public function __construct()
    {
        $this->createApplication();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelDataServiceProvider::class,
        ];
    }

    public function setup()
    {
        app(DataConfig::class)->getDataClass(ComplicatedData::class)->prepareForCache();
        app(DataConfig::class)->getDataClass(SimpleData::class)->prepareForCache();
        app(DataConfig::class)->getDataClass(MultiNestedData::class)->prepareForCache();
        app(DataConfig::class)->getDataClass(NestedData::class)->prepareForCache();
    }

    #[Revs(500), Iterations(2)]
    public function benchDataCreation()
    {
        MultiNestedData::from([
            'nested' => ['simple' => 'Hello'],
            'nestedCollection' => [
                ['simple' => 'I'],
                ['simple' => 'am'],
                ['simple' => 'groot'],
            ],
        ]);
    }

    #[Revs(500), Iterations(2)]
    public function benchDataTransformation()
    {
        $data = new MultiNestedData(
            new NestedData(new SimpleData('Hello')),
            [
                new NestedData(new SimpleData('I')),
                new NestedData(new SimpleData('am')),
                new NestedData(new SimpleData('groot')),
            ]
        );

        $data->toArray();
    }

    #[Revs(500), Iterations(2)]
    public function benchDataCollectionCreation()
    {
        $collection = Collection::times(
            15,
            fn() => [
                'withoutType' => 42,
                'int' => 42,
                'bool' => true,
                'float' => 3.14,
                'string' => 'Hello world',
                'array' => [1, 1, 2, 3, 5, 8],
                'nullable' => null,
                'mixed' => 42,
                'explicitCast' => '16-06-1994',
                'defaultCast' => '1994-05-16T12:00:00+01:00',
                'nestedData' => [
                    'string' => 'hello',
                ],
                'nestedCollection' => [
                    ['string' => 'never'],
                    ['string' => 'gonna'],
                    ['string' => 'give'],
                    ['string' => 'you'],
                    ['string' => 'up'],
                ],
                'nestedArray' => [
                    ['string' => 'never'],
                    ['string' => 'gonna'],
                    ['string' => 'give'],
                    ['string' => 'you'],
                    ['string' => 'up'],
                ],
            ]
        )->all();

        ComplicatedData::collect($collection, DataCollection::class);
    }

    #[Revs(500), Iterations(2)]
    public function benchDataCollectionTransformation()
    {
        $collection = Collection::times(
            15,
            fn() => new ComplicatedData(
                42,
                42,
                true,
                3.14,
                'Hello World',
                [1, 1, 2, 3, 5, 8],
                null,
                Optional::create(),
                42,
                CarbonImmutable::create(1994,05,16),
                new DateTime('1994-05-16T12:00:00+01:00'),
                new SimpleData('hello'),
                new DataCollection(NestedData::class, [
                    new NestedData(new SimpleData('I')),
                    new NestedData(new SimpleData('am')),
                    new NestedData(new SimpleData('groot')),
                ]),
                [
                    new NestedData(new SimpleData('I')),
                    new NestedData(new SimpleData('am')),
                    new NestedData(new SimpleData('groot')),
                ],
            )
        )->all();

        $dataCollection = (new DataCollection(ComplicatedData::class, $collection));

        $dataCollection->toArray();
    }
}
