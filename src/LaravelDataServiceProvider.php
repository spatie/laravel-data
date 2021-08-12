<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Resolvers\DataFromRequestResolver;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelDataServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-data')
            ->hasConfigFile();
    }

    public function packageRegistered()
    {
        $this->app->singleton(
            DataConfig::class,
            fn () => new DataConfig(config('data'))
        );

        /** @psalm-suppress UndefinedInterfaceMethod */
        $this->app->beforeResolving(Data::class, function ($class) {
            if ($this->app->has($class)) {
                return;
            }

            $this->app->bind(
                $class,
                fn () => $this->app->make(DataFromRequestResolver::class)->get($class),
            );
        });
    }
}
