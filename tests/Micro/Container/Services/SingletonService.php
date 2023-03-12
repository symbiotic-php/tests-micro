<?php

namespace Symbiotic\Tests\Micro\Container\Services;




use Symbiotic\Container\DIContainerInterface;

class SingletonService implements SingletonServiceInterface
{
    protected static $count_instances = 0;

    protected $container = null;

    public function __construct(DIContainerInterface $app, string $test_data)
    {
        static::$count_instances++;
    }

    public static function clearCountInstances()
    {
        static::$count_instances = 0;
    }

    public function getCountInstances()
    {
        return static::$count_instances;
    }
}
