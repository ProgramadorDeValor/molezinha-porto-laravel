<?php

namespace Molezinha\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Molezinha\Abstracts\Events\Providers\EventServiceProvider;
use Molezinha\Commands\CreateContainerCommand;
use Molezinha\Commands\CreateMigrationCommand;
use Molezinha\Commands\CreateModelCommand;
use Molezinha\Commands\CreateApiRequestCommand;
use Molezinha\Commands\RunContainersSeeders;
use Molezinha\Core\Molezinha;
use Molezinha\Generator\GeneratorsServiceProvider;
use Molezinha\Loaders\AliasesLoaderTrait;
use Molezinha\Loaders\AutoLoaderTrait;
use Molezinha\Loaders\ProvidersLoaderTrait;

// use App\Ship\Providers\ShipProvider;
// use App\Ship\Parents\Providers\RoutesProvider;
use Barryvdh\Cors\ServiceProvider as CorsServiceProvider;


/**
 * Class MolezinhaServiceProvider
 * @package Molezinha\Providers
 *
 * Inspired by : https://github.com/apiato/core/blob/master/Generator/GeneratorsServiceProvider.php
 *
 */
class MolezinhaServiceProvider extends ServiceProvider
{
    use ProvidersLoaderTrait;
    use AliasesLoaderTrait;
    use AutoLoaderTrait;

    public $serviceProviders = [
        // Third Party Packages Providers:
        // HashidsServiceProvider::class,
        // RepositoryServiceProvider::class,
        CorsServiceProvider::class,
        // FractalServiceProvider::class,
        // HeimdalExceptionsServiceProvider::class,
        // add the Laravel Tinker Service Provider
        // TinkerServiceProvider::class,
        // Internal Apiato Providers:
        RoutesProvider::class, // exceptionally adding the Route Provider, unlike all other providers in the parents.
        //ShipProvider::class, // the ShipProvider for the Ship third party packages.
        GeneratorsServiceProvider::class, // the code generator provider.
    ];
    /**
     * Register any Alias on the Ship layer (including third party packages).
     *
     * @var  array
     */
    protected $aliases = [
        // 'Hashids' => Hashids::class,
        // 'Fractal' => FractalFacade::class,
    ];

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../Config/molezinha.php' => config_path('molezinha.php'),
        ]);

        //Load Containers and Ship Components
        $this->runLoadersBoot();

        //load all service providers defined
        $this->loadServiceProviders();
        $this->loadAliases();

        // load all service providers defined in this class
        //parent::boot();
    }

    public function register()
    {
        // The custom eventserviceprovider
        // Todo: Fiz issues related to the EventServiceProvider Class
        //App::register(EventServiceProvider::class);
        $this->registerCommands([
            CreateMigrationCommand::class,
            CreateContainerCommand::class,
            CreateModelCommand::class,
            CreateApiRequestCommand::class,
            RunContainersSeeders::class
        ]);
        $this->registerMolezinha();

    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerMolezinha()
    {
        $this->app->bind('molezinha', function ()
        {
            return new Molezinha();
        });

        $this->app->alias('molezinha', 'Molezinha\Core\Molezinha');
    }

    /**
     * Register the commandsrising.
     * @param array $classes
     */
    protected function registerCommands(array $classes)
    {
        foreach ($classes as $class)
        {
            $lowerClass = strtolower($class);
            $this->app->singleton("command.molezinha.$lowerClass", function ($app) use ($class)
            {
                return $app[$class];
            });
            $this->commands("command.molezinha.$lowerClass");
        }
    }
}
