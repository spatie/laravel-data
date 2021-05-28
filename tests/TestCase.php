<?php

namespace Spatie\LaravelData\Tests;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Model::unguard();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Spatie\\LaravelData\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
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

        /*
        include_once __DIR__.'/../database/migrations/create_laravel-data-resource_table.php.stub';
        (new \CreatePackageTable())->up();
        */
    }

    public function faker(): Generator
    {
        return FakerFactory::create();
    }
}
