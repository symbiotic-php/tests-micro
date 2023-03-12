<?php

namespace Symbiotic\Tests\Micro\Container\Classes;


use Symbiotic\Container\Container;
use Symbiotic\Container\ServiceContainerInterface;
use Symbiotic\Container\ServiceContainerTrait;

class ServiceContainer extends Container implements ServiceContainerInterface
{
    use ServiceContainerTrait;

    public function __construct()
    {
        $this->dependencyInjectionContainer = $this;
    }

}