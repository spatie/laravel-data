<?php

namespace Spatie\LaravelData;

use Illuminate\Support\Collection;
use Spatie\LaravelAutoDiscoverer\Discover;
use Spatie\LaravelData\Commands\DataMakeCommand;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Support\Cache\DataCacheManager;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelDataServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-data')
            ->hasCommand(DataMakeCommand::class)
            ->hasConfigFile();
    }

    public function packageRegistered()
    {
        $this->app->singleton(
            DataConfig::class,
            fn () => new DataConfig(config('data'))
        );

        Discover::classes('laravel-data')
            ->within(...app(DataConfig::class)->getDataPaths())
            ->extending(DataObject::class)
            ->returnReflection()
            ->get(fn(array $classes) => app(DataCacheManager::class)->initialize($classes));

        /** @psalm-suppress UndefinedInterfaceMethod */
        $this->app->beforeResolving(BaseData::class, function ($class, $parameters, $app) {
            if ($app->has($class)) {
                return;
            }

            $app->bind(
                $class,
                fn ($container) => $class::from($container['request'])
            );
        });
    }
}
