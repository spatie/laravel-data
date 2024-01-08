<?php

use Carbon\CarbonImmutable;
use Orchestra\Testbench\Concerns\CreatesApplication;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class SimpleDataBench
{
    use CreatesApplication;

    protected ComplicatedData $data;

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
        $this->data = new ComplicatedData(
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
            []
//            new SimpleData('hello'),
//            new DataCollection(NestedData::class, [
//                new NestedData(new SimpleData('I')),
//                new NestedData(new SimpleData('am')),
//                new NestedData(new SimpleData('groot')),
//            ]),
//            [
//                new NestedData(new SimpleData('I')),
//                new NestedData(new SimpleData('am')),
//                new NestedData(new SimpleData('groot')),
//            ],
        );

        app(DataConfig::class)->getDataClass(ComplicatedData::class);
        app(DataConfig::class)->getDataClass(SimpleData::class);
    }

    #[Revs(5000), Iterations(5), BeforeMethods('setup')]
    public function benchDataTransformation()
    {
        $this->data->toArray();
    }

    #[Revs(5000), Iterations(5), BeforeMethods('setup')]
    public function benchDataManualTransformation()
    {
        $this->data->toUserDefinedToArray();
    }
}
