<?php

namespace Spatie\LaravelData\Tests;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\Snapshots\MatchesSnapshots;

class TestCase extends Orchestra
{
    use MatchesSnapshots;

    public function setUp(): void
    {
        parent::setUp();

        Model::unguard();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelDataServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }

    public function faker(): Generator
    {
        return FakerFactory::create();
    }
}
