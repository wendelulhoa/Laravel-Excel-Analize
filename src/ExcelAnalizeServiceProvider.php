<?php

namespace Analize\Excel;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Analize\Excel\Cache\CacheManager;
use Analize\Excel\Console\ExportMakeCommand;
use Analize\Excel\Console\ImportMakeCommand;
use Analize\Excel\Files\Filesystem;
use Analize\Excel\Files\TemporaryFileFactory;
use Analize\Excel\Mixins\DownloadCollection;
use Analize\Excel\Mixins\StoreCollection;
use Analize\Excel\Transactions\TransactionHandler;
use Analize\Excel\Transactions\TransactionManager;

class ExcelAnalizeServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Console/stubs/export.model.stub'       => base_path('stubs/export.model.stub'),
                __DIR__ . '/Console/stubs/export.plain.stub'       => base_path('stubs/export.plain.stub'),
                __DIR__ . '/Console/stubs/export.query.stub'       => base_path('stubs/export.query.stub'),
                __DIR__ . '/Console/stubs/export.query-model.stub' => base_path('stubs/export.query-model.stub'),
                __DIR__ . '/Console/stubs/import.collection.stub'  => base_path('stubs/import.collection.stub'),
                __DIR__ . '/Console/stubs/import.model.stub'       => base_path('stubs/import.model.stub'),
            ], 'stubs');

            if ($this->app instanceof LumenApplication) {
                $this->app->configure('excelAnalize');
            } else {
                $this->publishes([
                    $this->getConfigFile() => config_path('excelAnalize.php'),
                ], 'config');
            }
        }

        if ($this->app instanceof \Illuminate\Foundation\Application) {
            // Laravel
            $this->app->booted(function ($app) {
                $app->make(SettingsProvider::class)->provide();
            });
        } else {
            // Lumen
            $this->app->make(SettingsProvider::class)->provide();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->getConfigFile(),
            'excelAnalize'
        );

        $this->app->bind(CacheManager::class, function ($app) {
            return new CacheManager($app);
        });

        $this->app->singleton(TransactionManager::class, function ($app) {
            return new TransactionManager($app);
        });

        $this->app->bind(TransactionHandler::class, function ($app) {
            return $app->make(TransactionManager::class)->driver();
        });

        $this->app->bind(TemporaryFileFactory::class, function () {
            return new TemporaryFileFactory(
                config('excelAnalize.temporary_files.local_path', config('excelAnalize.exports.temp_path', storage_path('framework/laravel-excel'))),
                config('excelAnalize.temporary_files.remote_disk')
            );
        });

        $this->app->bind(Filesystem::class, function ($app) {
            return new Filesystem($app->make('filesystem'));
        });

        $this->app->bind('excelAnalize', function ($app) {
            return new ExcelAnalize(
                $app->make(Writer::class),
                $app->make(QueuedWriter::class),
                $app->make(Reader::class),
                $app->make(Filesystem::class)
            );
        });

        $this->app->alias('excelAnalize', Excel::class);
        $this->app->alias('excelAnalize', Exporter::class);
        $this->app->alias('excelAnalize', Importer::class);

        Collection::mixin(new DownloadCollection);
        Collection::mixin(new StoreCollection);

        $this->commands([
            ExportMakeCommand::class,
            ImportMakeCommand::class,
        ]);
    }

    /**
     * @return string
     */
    protected function getConfigFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'excelAnalize.php';
    }
}
