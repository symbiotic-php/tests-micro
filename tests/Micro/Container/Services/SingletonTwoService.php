<?php

namespace Symbiotic\Tests\Micro\Container\Services;


class SingletonTwoService implements SingletonServiceInterface
{
    protected static $count_instances = 0;

    public function __construct()
    {
        static::$count_instances++;
    }

    public function getCountInstances()
    {
        return static::$count_instances;
    }
}
