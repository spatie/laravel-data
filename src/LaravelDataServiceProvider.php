<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Commands\DataMakeCommand;
use Spatie\LaravelData\Commands\DataStructuresCacheCommand;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Support\Caching\DataStructureCache;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\VarDumper\VarDumperManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelDataServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-data')
            ->hasCommand(DataMakeCommand::class)
            ->hasCommand(DataStructuresCacheCommand::class)
            ->hasConfigFile('data');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(
            DataStructureCache::class,
            fn () => new DataStructureCache(config('data.structure_caching.cache'))
        );

        $this->app->singleton(
            DataConfig::class,
            fn () => $this->app->make(DataStructureCache::class)->getConfig() ?? DataConfig::createFromConfig(config('data'))
        );

        $this->app->beforeResolving(BaseData::class, function ($class, $parameters, $app) {
            $app->bind(
                $class,
                fn ($container) => $class::from($container['request'])
            );
        });
    }

    public function packageBooted(): void
    {
        $enableVarDumperCaster = match (config('data.var_dumper_caster_mode')) {
            'enabled' => true,
            'development' => $this->app->environment('local', 'testing'),
            default => false,
        };

        if ($enableVarDumperCaster) {
            (new VarDumperManager())->initialize();
        }
    }
}
