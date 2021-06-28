<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Support\DataTransformers;
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
            DataTransformers::class,
            fn () => new DataTransformers(config('data.transformers'))
        );
    }
}
