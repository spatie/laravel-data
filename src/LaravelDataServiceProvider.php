<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Casts\DateTimeCast;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataResolver;
use Spatie\LaravelData\Transformers\DateTransformer;
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
        $this->app->when(DateTimeCast::class)->needs('$format')->give(
            config('data.date_format')
        );

        $this->app->when(DateTransformer::class)->needs('$format')->give(
            config('data.date_format')
        );

        $this->app->singleton(
            DataConfig::class,
            fn () => new DataConfig(config('data'))
        );

        $this->app->beforeResolving(RequestData::class, function ($class) {
            if ($this->app->has($class)) {
                return;
            }

            $this->app->bind(
                $class,
                fn () => $this->app->make(DataResolver::class)->get($class),
            );
        });
    }
}
