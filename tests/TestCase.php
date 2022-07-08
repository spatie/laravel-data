<?php

namespace Spatie\LaravelData\Tests;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelData\Support\Validation\ValidationRule;
use Spatie\Snapshots\MatchesSnapshots;

class TestCase extends Orchestra
{
    use MatchesSnapshots;
    use RefreshDatabase;

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

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
    }

    public function faker(): Generator
    {
        return FakerFactory::create();
    }

    public function assertValidationAttributeRules(
        array $expected,
        ValidationRule $attribute
    ) {
        $this->assertEquals($expected, $attribute->getRules());
    }

    public function onlyPHP81()
    {
        if (version_compare(phpversion(), '8.1', '<')) {
            $this->markTestIncomplete('No enum support in PHP 8.1');
        }
    }
}
