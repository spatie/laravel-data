<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Orchestra\Testbench\Concerns\CreatesApplication;
use PhpBench\Attributes\Assert;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class DataBench
{
    use CreatesApplication;

    protected DataCollection $collection;

    protected Data $object;

    protected array $collectionPayload;

    protected array $objectPayload;

    private DataConfig $dataConfig;

    public function __construct()
    {
        $this->createApplication();
        $this->dataConfig = app(DataConfig::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelDataServiceProvider::class,
        ];
    }

    public function setupCache()
    {
        $this->dataConfig->getDataClass(ComplicatedData::class)->prepareForCache();
        $this->dataConfig->getDataClass(SimpleData::class)->prepareForCache();
        $this->dataConfig->getDataClass(NestedData::class)->prepareForCache();
    }

    public function setupCollectionTransformation()
    {
        $collection = Collection::times(
            15,
            fn () => new ComplicatedData(
                42,
                42,
                true,
                3.14,
                'Hello World',
                [1, 1, 2, 3, 5, 8],
                null,
                Optional::create(),
                42,
                CarbonImmutable::create(1994, 05, 16),
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
            ));

        $this->collection = new DataCollection(ComplicatedData::class, $collection);
    }

    public function setupObjectTransformation()
    {
        $this->object = new ComplicatedData(
            42,
            42,
            true,
            3.14,
            'Hello World',
            [1, 1, 2, 3, 5, 8],
            null,
            Optional::create(),
            42,
            CarbonImmutable::create(1994, 05, 16),
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
        );
    }

    public function setupCollectionCreation()
    {
        $this->collectionPayload = Collection::times(
            15,
            fn () => [
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
    }

    public function setupObjectCreation()
    {
        $this->objectPayload = [
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
        ];
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupCollectionTransformation']),
        Assert('mode(variant.time.avg) < 590 microseconds +/- 5%')
    ]
    public function benchCollectionTransformation()
    {
        $this->collection->toArray();
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupObjectTransformation']),
        Assert('mode(variant.time.avg) < 39 microseconds +/- 5%')
    ]
    public function benchObjectTransformation()
    {
        $this->object->toArray();
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupCollectionCreation']),
        Assert('mode(variant.time.avg) < 1.335 milliseconds +/- 5%')
    ]
    public function benchCollectionCreation()
    {
        ComplicatedData::collect($this->collectionPayload, DataCollection::class);
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupCache', 'setupObjectCreation']),
        Assert('mode(variant.time.avg) < 90 microseconds +/- 5%')
    ]
    public function benchObjectCreation()
    {
        ComplicatedData::from($this->objectPayload);
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCollectionTransformation']),
        Assert('mode(variant.time.avg) < 791 microseconds +/- 10%')
    ]
    public function benchCollectionTransformationWithoutCache()
    {
        $this->collection->toArray();

        $this->dataConfig->reset();
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupObjectTransformation']),
        Assert('mode(variant.time.avg) < 226 microseconds +/- 10%')
    ]
    public function benchObjectTransformationWithoutCache()
    {
        $this->object->toArray();

        $this->dataConfig->reset();
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods(['setupCollectionCreation']),
        Assert('mode(variant.time.avg) < 1.62 milliseconds +/- 10%')
    ]
    public function benchCollectionCreationWithoutCache()
    {
        ComplicatedData::collect($this->collectionPayload, DataCollection::class);

        $this->dataConfig->reset();
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods(['setupObjectCreation']),
        Assert('mode(variant.time.avg) < 347 microseconds +/- 10%')
    ]
    public function benchObjectCreationWithoutCache()
    {
        ComplicatedData::from($this->objectPayload);

        $this->dataConfig->reset();
    }
}
