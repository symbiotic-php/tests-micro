<?php

namespace Symbiotic\Tests\Micro\Container;

use Symbiotic\Container\Container;
use Symbiotic\Container\DIContainerInterface;
use PHPUnit\Framework\TestCase as UnitTestCase;


abstract class TestCase extends UnitTestCase
{
    /**
     * @var DIContainerInterface
     */
    protected $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }




}
