<?php

namespace Spatie\LaravelData;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelDataServiceProvider extends PackageServiceProvider
{
    public function register()
    {
        $this->app->singleton(new DataTransformers(config('data.transformers')));
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-data')
            ->hasConfigFile();
    }
}
