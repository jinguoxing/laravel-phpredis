<?php

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    |
    */

return [
    
	'redis' => [

    'cluster' => false, // if true a RedisArray will be created


    'default' => [
        'host'       => '127.0.0.1', // default: '127.0.0.1'
        'port'       => 6379,        // default: 6379
        'prefix'     => 'myapp:',    // default: ''

    //  Change the selected database for the current connection.
        'database'   => 0,           // default: 0
        'timeout'    => 0,         // default: 0 (no timeout)
        'serializer' => 'none'   // default: 'none', possible values: 'none', 'php', 'igbinary'
    ],
],
];