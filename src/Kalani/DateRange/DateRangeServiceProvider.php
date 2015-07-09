<?php namespace Kalani\DateRange;

use Illuminate\Support\ServiceProvider;
use Kalani\DateRange\DateRange;


class DateRangeServiceProvider extends ServiceProvider 
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot()
	{
	    $this->publishes([
	        __DIR__.'/../../config/date-range.php' => config_path('date-range.php'),
	    ]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['date-range'] = $this->app->share(function($app){
			$config = $this->app['config'];
			return new DateRange($config);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('date-range');
	}

}
