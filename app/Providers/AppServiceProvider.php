<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Taco\Scheduling\TasksDeposit;
use Taco\Scheduling\TasksDepositPhpBased;


class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	function register()
	{
		// Vytáhnem si configurator abychom nemuseli přidávat všechny ty služby při každé změně.
		if (config('services.scheduling.configurator')) {
			$this->app->singleton(TasksDeposit::class, function ($app) {
				return new TasksDepositPhpBased(config('services.scheduling.task_bank'));
			});
			$this->app->make(config('services.scheduling.configurator'))->configure($this->app);
		}
	}



    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
