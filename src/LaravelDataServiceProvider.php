<?php

namespace Spatie\LaravelData;

use Illuminate\Http\Request;
use Spatie\LaravelData\Console\Commands\MakeDataCommand;
use Spatie\LaravelData\Console\DataQualifier;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelDataServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-data')
            ->hasConfigFile()
            ->hasCommands(MakeDataCommand::class);
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
                fn () => $class::from($this->app->make(Request::class)),
            );
        });

        $this->app->bind(DataQualifier::class, config('data.qualifier'));
    }

    public function packageBooted()
    {
        $this->publishes([
            __DIR__ . '/Console/stubs' => $this->app->basePath('stubs'),
        ], 'laravel-data-stubs');
    }
}
