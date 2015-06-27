<?php

namespace KingNet\PhpRedis;

use Illuminate\Support\Facades\Facade as PhpRedisFacade;

class Facede extends PhpRedisFacade {


	/**
     * 默认为 Server
     *
     * @return string
     */
    static public function getFacadeAccessor()
    {
        return "phpredis";
    }

}