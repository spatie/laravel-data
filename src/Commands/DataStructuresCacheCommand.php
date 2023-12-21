<?php

namespace Spatie\LaravelData\Commands;

use Illuminate\Console\Command;
use ReflectionClass;
use Spatie\LaravelData\Support\Caching\CachedDataConfig;
use Spatie\LaravelData\Support\Caching\DataClassFinder;
use Spatie\LaravelData\Support\Caching\DataStructureCache;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;

class DataStructuresCacheCommand extends Command
{
    protected $signature = 'data:cache-structures {--show-classes : Show the data classes cached}';

    protected $description = 'Cache the internal data structures';

    public function handle(
        DataStructureCache $dataStructureCache,
        DataConfig $dataConfig
    ): void {
        $this->components->info('Caching data structures...');

        $dataClasses = DataClassFinder::fromConfig(config('data.structure_caching'))->classes();

        $cachedDataConfig = CachedDataConfig::initialize($dataConfig);

        $dataStructureCache->storeConfig($cachedDataConfig);

        $progressBar = $this->output->createProgressBar(count($dataClasses));

        foreach ($dataClasses as $dataClass) {
            $dataStructureCache->storeDataClass(
                DataClass::create(new ReflectionClass($dataClass))
            );

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->line(PHP_EOL);
        $this->line('Cached '.count($dataClasses).' data classes');

        if ($this->option('show-classes')) {
            $this->table(
                ['Data Class'],
                array_map(fn (string $dataClass) => [$dataClass], $dataClasses)
            );
        }
    }
}
