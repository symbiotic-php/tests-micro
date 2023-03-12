<?php
declare(strict_types=1);

namespace Symbiotic\Tests\Micro\Container {

    use Symbiotic\Container\ServiceContainerInterface;
    use Symbiotic\Tests\Micro\Container\Classes\ServiceContainer;
    use Symbiotic\Tests\Micro\Container\Providers\TestsProvider;


    /**
     * Class ServiceContainerTest
     * @package Symbiotic\Tests\Micro\Container
     * @property ServiceContainerInterface $container
     *
     * @covers ServiceContainerInterface
     */
    class ServiceContainerTest extends TestCase
    {

        protected function setUp(): void
        {
            $this->container = new ServiceContainer();
        }

        public function testRegister(): void
        {
            //Register
            $instance = $this->container->register(TestsProvider::class);
            $provider = $this->container->getProvider(TestsProvider::class);
            $this->assertEquals($instance, $provider);

            $this->container->clear();
        }
    }
}



