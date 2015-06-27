<?php 

namespace Kingnet\PhpRedis;

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
        $cluster = Arr::pull($servers, 'cluster');

        $options = (array) Arr::pull($servers, 'options');

        if ($cluster) {
            $this->clients = $this->createAggregateClient($servers, $options);
        } else {
            $this->clients = $this->createSingleClients($servers, $options);
        }
    }

    /**
     * Create a new aggregate client supporting sharding.
     *
     * @param  array  $servers
     * @param  array  $options
     * @return array
     */
    protected function createAggregateClient(array $servers, array $options = [])
    {

         $options = array(
            'lazy_connect' => true,
            'pconnect'     => false,
            'timeout'      => 0,
        );
        $cluster = array();
        foreach ($servers as $key => $server) {
            if ($key === 'cluster') continue;
            $host    = empty($server['default']['host'])    ? '127.0.0.1' : $server['host'];
            $port    = empty($server['port'])    ? '6379'      : $server['port'];
            $serializer = Redis::SERIALIZER_NONE;
            if (!empty($server['serializer'])) {
                if ($server['serializer'] === 'none') {
                    $serializer = Redis::SERIALIZER_PHP;
                } else if ($server['serializer'] === 'igbinary') {
                    if (defined('Redis::SERIALIZER_IGBINARY')) {
                        $serializer = Redis::SERIALIZER_IGBINARY;
                    } else {
                        $serializer = Redis::SERIALIZER_PHP;
                    }
                }
            }
            $cluster[$host.':'.$port] = array(
                'prefix'     => empty($server['prefix'])   ? '' : $server['prefix'],
                'database'   => empty($server['database']) ? 0  : $server['database'],
                'serializer' => $serializer,
            );
            if (isset($server['persistent'])) {
                $options['pconnect'] = $options['pconnect'] && $server['persistent'];
            } else {
                $options['pconnect'] = false;
            }
            if (!empty($server['timeout'])) {
                $options['timeout'] = max($options['timeout'], $server['timeout']);
            }
        }
        $ra = new RedisArray(array_keys($cluster), $options);
        foreach ($cluster as $host => $options) {
            $redis = $ra->_instance($host);
            $redis->setOption(Redis::OPT_PREFIX, $options['prefix']);
            $redis->setOption(Redis::OPT_SERIALIZER, $options['serializer']);
            $redis->select($options['database']);
        }
        return array('default' => $ra);




    }

    /**
     * Create an array of single connection clients.
     *
     * @param  array  $servers
     * @param  array  $options
     * @return array
     */
    protected function createSingleClients(array $servers, array $options = [])
    {
        $clients = [];

        foreach ($servers as $key => $server) {
            if('cluster'===$key) continue;

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