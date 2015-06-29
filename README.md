# laravel-phpredis
laravel 5.1 
======
PhpRedis
The phpredis extension provides an API for communicating with the Redis key-value store. It is released under the PHP License, version 3.01. This code has been developed and maintained by Owlient from November 2009 to March 2011.

Requirements
======
PHP 5.4+<br/>
Laravel 5.x

Installation
======
```php	    
	    
"require": {
    "kingnet/laravel-phpredis":"dev-master"
}

```

Add the PhpRedisServiceProvider to config/app.php (comment out built-in RedisServiceProvider):

```php
// Illuminate\Redis\RedisServiceProvider::class,
//phpredis provider
    KingNet\PhpRedis\PhpRedisServiceProvider::class,

```

The default Facade alias conflicts with the Redis class provided by PhpRedis. To fix this, rename the alias in config/app.php:

```php
'PhpRedis'  => KingNet\PhpRedis\Facede::class,
```
Finally run composer update to update and install everything.


Configuration
===============
Just use php artisan vendor:publish and a phpredis.php file will be created in your config directory.

Usage
================

```php
use PhpRedis;


class PhpredisController extends Controller
{
  
    public function test()
    {

        PhpRedis::set('myname','kingnet');
        dd(PhpRedis::get('myname'));

    }

    
}
```


License
=============
This is free software distributed under the terms of the MIT license.

Contribution guidelines
==============

Please report any issue you find in the issues page.<br/>
Pull requests are welcome.
