<?php 

namespace KingNet\PhpRedis;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Redis\Database as DatabaseContracts;
use \Redis;
use \RedisArray;


class Database implements DatabaseContract
{
    /**
     * The host address of the database.
     *
     * @var array
     */
    protected $clients;

    /**
     * Create a new Redis connection instance.
     *
     * @param  array  $servers
     * @return void
     */
    public function __construct(array $servers = [])
    {

        $options = (array) Arr::pull($servers, 'options');

        $this->clients = $this->createSingleClients($servers, $options);
    
    }

    /**
     * Create an array of single connection clients.
     *
     * @param  array  $servers
     * @param  array  $options
     * @return array
     */
    protected function createSingleClients(array $servers, array $options =[])
    {
        $clients = [];

        foreach ($servers as $key => $server) {
           
            $phpredis = new Redis();
            $host    = empty($server['host']) ? '127.0.0.1' : $server['host'];
            $port    = empty($server['port'])?'6379':$server['port']; 
            $timeout = empty($server['timeout'])?0:$server['timeout'];

            
            if (isset($server['persistent']) && $server['persistent']) {
                $phpredis->pconnect($host,$port,$timeout);
            } else {
                $phpredis->connection($host,$port,$timeout);
            }

            if (isset($server['prefix']) && !empty($server['prefix']))
              {
                $phpredis->setOption(Redis::OPT_PREFIX,$server['prefix']);
              }

            //   redis的数据库默认是从0 - 16 
            if (isset($server['database'] && (int)$server['database']>=0 && (int)$server['database']<=16)
            {
                $phpredis->select($server['database']);
            }

            if (!empty($server['serializer'])) 
            {
                $serializer = Redis::SERIALIZER_NONE;
                if ($server['serializer'] === 'php')
                {
                    $serializer = Redis::SERIALIZER_PHP;
                } 
                elseif ($server['serializer'] === 'igbinary') 
                {
                    if (defined('Redis::SERIALIZER_IGBINARY')) 
                    {
                        $serializer = Redis::SERIALIZER_IGBINARY;
                    } 
                    else 
                    {
                        $serializer = Redis::SERIALIZER_PHP;
                    }
                }
                $phpredis->setOption(Redis::OPT_SERIALIZER, $serializer);
            }

            $clients['$key'] = $phpredis;

        }

        return $clients;
    }

    /**
     * Get a specific Redis connection instance.
     *
     * @param  string  $name
     * @return \redis\ClientInterface|null
     */
    public function connection($name = 'default')
    {
        return Arr::get($this->clients, $name ?: 'default');
    }

    /**
     * Run a command against the Redis database.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function command($method, array $parameters = [])
    {
        return call_user_func_array([$this->clients['default'], $method], $parameters);
    }

    /**
     * Dynamically make a Redis command.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->command($method, $parameters);
    }
}