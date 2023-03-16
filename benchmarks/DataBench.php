<?php

use Illuminate\Support\Collection;
use Orchestra\Testbench\Concerns\CreatesApplication;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Subject;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\MultiNestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

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

    #[Revs(500), Iterations(2)]
    public function benchData()
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
    public function benchDataCollection()
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
            ]
        )->all();

        ComplicatedData::collection($collection);
    }
}
