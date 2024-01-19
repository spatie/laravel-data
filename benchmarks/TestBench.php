<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Orchestra\Testbench\Concerns\CreatesApplication;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Types\Storage\AcceptedTypesStorage;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class TestBench
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

    #[Revs(5000), Iterations(5)]
    public function benchUseStored()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->runStored();
        }
    }

    protected function runStored(): array
    {
        return AcceptedTypesStorage::getAcceptedTypes(Collection::class);
    }

    #[Revs(5000), Iterations(5)]
    public function benchUseNative()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->runNative();
        }
    }

    protected function runNative(): array
    {
        return  ! class_exists(Collection::class) ? [] :  array_unique([
            ...array_values(class_parents(Collection::class)),
            ...array_values(class_implements(Collection::class)),
        ]);
    }
}
