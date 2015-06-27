<?php

namespace KingNet\PhpRedis;

use Illuminate\Support\ServiceProvider;

class PhpRedisServiceProvider extends ServiceProvider {
	
	protected $defer = true;

	/**
     * Boot the provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/phpredis.php' => config_path('phpredis.php'),
        ], 'config');
    }

    /**
     * Register the provider
     * @return void
     */
	public function register() {
		$this->app['phpredis'] = $this->app->share(function($app) {
			return new Database($app['config']['phpredis.redis']);
		});
	}


	/**
     * 提供的服务
     *
     * @return array
     */
	public function provides() {
		return array('phpredis');
	}
}