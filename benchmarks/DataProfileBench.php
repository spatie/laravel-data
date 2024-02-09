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

class DataProfileBench
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

        $this->setupCache();;
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
                null,
                null,
                [],
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
            null,
            null,
            [],
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
                'nestedData' => null,
                'nestedCollection' => null,
                'nestedArray' => [],
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
            'nestedData' => null,
            'nestedCollection' => null,
            'nestedArray' => [],
        ];
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods([ 'setupCollectionTransformation']),
    ]
    public function benchProfileCollectionTransformation()
    {
        $this->collection->toArray();
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods([ 'setupObjectTransformation']),
    ]
    public function benchProfileObjectTransformation()
    {
        $this->object->toArray();
    }

    #[
        Revs(500),
        Iterations(5),
        BeforeMethods([ 'setupCollectionCreation']),
    ]
    public function benchProfileCollectionCreation()
    {
        ComplicatedData::collect($this->collectionPayload, DataCollection::class);
    }

    #[
        Revs(5000),
        Iterations(5),
        BeforeMethods([ 'setupObjectCreation']),
    ]
    public function benchProfileObjectCreation()
    {
        ComplicatedData::from($this->objectPayload);
    }
}
