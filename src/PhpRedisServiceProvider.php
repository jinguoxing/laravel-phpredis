<?php

namespace Kingnet\PhpRedis;

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
            __DIR__ . '/config.php' => config_path('phpredis.php'),
        ], 'config');
    }

    /**
     * Register the provider
     * @return void
     */
	public function register() {
		$this->app['redis'] = $this->app->share(function($app) {
			return new Database($app['config']['database.redis']);
		});
	}


	/**
     * 提供的服务
     *
     * @return array
     */
	public function provides() {
		return array('redis');
	}
}