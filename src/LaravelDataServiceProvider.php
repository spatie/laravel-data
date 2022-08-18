<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Commands\DataMakeCommand;
use Spatie\LaravelData\Contracts\BaseData;
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
            ->hasConfigFile('data');
    }

    public function packageRegistered()
    {
        $this->app->singleton(
            DataConfig::class,
            fn () => new DataConfig(config('data'))
        );

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

    public function packageBooted()
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
