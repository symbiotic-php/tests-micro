<?php

namespace {

    use Symbiotic\Tests\Micro\Container\Classes\TestClass;

    /**
     * @return string  ('testFunction')
     */
    function testFunction()
    {
        return __FUNCTION__;
    }

    /**
     * Функция в глобальной области видимости для теста
     *
     * @param string $param1
     * @param string $param2
     *
     * @used-by ContainerTest::testCallFunction()
     * @return string
     */
    function testFunctionWithStringParams(string $param1, string $param2)
    {
        return $param1 . $param2;
    }

    /**
     * Функция в глобальной области видимости для теста
     *
     * @param string $param1
     *
     * @used-by ContainerTest::testCallFunction()
     * @return string
     */
    function testFunctionWithDefaultStringParam(string $param1 = __FUNCTION__)
    {
        return $param1;
    }

    /**
     * Функция в глобальной области видимости для теста
     *
     * @param TestClass $param1
     *
     * @used-by ContainerTest::testCallFunction()
     * @return TestClass
     */
    function testFunctionWithClassParam(TestClass $param1): TestClass
    {
        return $param1;
    }

    /**
     * Функция в глобальной области видимости для теста
     *
     * @param TestClass $param1
     *
     * @used-by ContainerTest::testCallFunction()
     * @return TestClass
     */
    function testFunctionWithDefaultClassParam(TestClass $param1 = null): ?TestClass
    {
        return $param1;
    }
}


namespace Symbiotic\Tests\Micro\Container {

    use Symbiotic\Container\Container;
    use Symbiotic\Container\DIContainerInterface;
    use Symbiotic\Tests\Micro\Container\Classes\TestClass;
    use Symbiotic\Tests\Micro\Container\Services\AuthService;
    use Symbiotic\Tests\Micro\Container\Services\SessionService;
    use Symbiotic\Tests\Micro\Container\Services\SessionServiceDecorator;
    use Symbiotic\Tests\Micro\Container\Services\SingletonService;
    use Symbiotic\Tests\Micro\Container\Services\SingletonServiceInterface;

    /**
     * @covers \Symbiotic\Container\ContainerTrait
     * @covers \Symbiotic\Container\Container
     */
    class ContainerTest extends TestCase
    {


        /**
         * @return void
         * @throws \Symbiotic\Container\BindingResolutionException
         * @throws \Symbiotic\Container\NotFoundException
         */
        public function testMake(): void
        {
            // Простое создание объекта без параметров
            $instance = $this->container->make(SessionService::class);
            $this->assertInstanceOf(SessionService::class, $instance);

            // Создание объекта с передачей параметров
            // SingletonService::__construct(Container $app, string $test_data)

            // Передача параметров по имени
            $instance = $this->container->make(
                SingletonService::class,
                ['test_data' => 'test', 'app' => $this->container]
            );
            $this->assertInstanceOf(SingletonService::class, $instance);


            // $app добавлен автоматически
            $instance = $this->container->make(SingletonService::class, ['test_data' => 'test']);
            $this->assertInstanceOf(SingletonService::class, $instance);

            // Передача параметров по номеру, отсчет от 0
            $instance = $this->container->make(SingletonService::class, [0 => $this->container, 1 => 'test']);
            $this->assertInstanceOf(SingletonService::class, $instance);

            // Передача параметров по номеру, $app добавлен автоматически
            $instance = $this->container->make(SingletonService::class, [1 => 'test']);
            $this->assertInstanceOf(SingletonService::class, $instance);

            $this->container->clear();
        }

        /**
         * @return void
         * @throws \Symbiotic\Container\BindingResolutionException
         * @throws \Symbiotic\Container\NotFoundException
         */
        public function testMakeExceptionNotParam(): void
        {
            $this->expectException(\ArgumentCountError::class);
            // Выброс исключения при отсутствии обязательного параметра
            $this->container->make(SingletonService::class);
            $this->container->clear();
        }

        public function testMakeExceptionInvalidParam(): void
        {
            $this->expectException(\TypeError::class);
            // Выброс исключения при отсутствии обязательного параметра
            $this->container->make(SingletonService::class, ['test_data' => 'test', 'app' => 'Exception!!!']);

            $this->container->clear();
        }

        public function testAlias(): void
        {
            $this->container->alias(SingletonService::class, 'alias_class');
            $interface_object = $this->container->make('alias_class', ['app' => $this->container, 'test_data' => 'test']
            );

            $this->assertInstanceOf(SingletonService::class, $interface_object);

            $this->container->clear();
        }

        public function testBind(): void
        {
            $this->container->bind(SingletonServiceInterface::class, SingletonService::class);
            $interface_object = $this->container->make(
                SingletonServiceInterface::class,
                ['app' => $this->container, 'test_data' => 'test']
            );

            $this->assertInstanceOf(SingletonService::class, $interface_object);

            $this->container->clear();
        }


        public function testMakeWithBind(): void
        {
            $this->container['test_data'] = 'test_string';

            $this->container->bind(SingletonService::class, function (DIContainerInterface $app) {
                return new SingletonService($app, $app->get('test_data'));
            });

            $this->container->alias(SingletonService::class, SingletonServiceInterface::class);
            $this->container->alias(SingletonServiceInterface::class, 'singleservice');


            $interface_object = $this->container->make(SingletonServiceInterface::class);
            $classname_object = $this->container->make(SingletonService::class);
            $alias_object = $this->container->make('singleservice');

            // test interface make
            $this->assertInstanceOf(SingletonService::class, $interface_object);
            // test classname make
            $this->assertInstanceOf(SingletonService::class, $classname_object);
            // test alias make
            $this->assertInstanceOf(SingletonService::class, $alias_object);


            // Test new instance
            $interface_object->test = 1;
            $classname_object->test = 2;
            $alias_object->test = 3;

            $this->assertNotEquals($interface_object, $classname_object);
            $this->assertNotEquals($classname_object, $alias_object);

            $this->container->clear();
        }

        public function testRebinding(): void
        {
            $this->container->singleton(AuthService::class, function (Container $container) {
                $auth = new AuthService(
                    $container->instance(SessionService::class, new SessionService(['name' => 'empty']))
                );
                // При обновлении объекта в контейнере, обновляем его  у нас
                $container->rebinding(SessionService::class, function ($container, $session) use ($auth) {
                    $auth->setSession($session);
                });

                return $auth;
            });

            /**
             * @var AuthService $auth
             */
            $auth = $this->container->make(AuthService::class);

            $this->assertEquals('empty', $auth->getName());

            $this->container->instance(SessionService::class, new SessionService(['name' => 'Alex']));
            $this->assertEquals('Alex', $auth->getName());

            $this->container->clear();
        }

        public function testResolving()
        {
            $this->container->resolving(
                AuthService::class,
                function (AuthService $auth, DIContainerInterface $container) {
                    $auth->setSession(
                        $container->make(SessionService::class, [['name' => 'Dave']])
                    );
                }
            );

            $auth = $this->container->make(AuthService::class);
            $this->assertEquals('Dave', $auth->getName());

            $this->container->clear();
        }

        public function testAfterResolving()
        {
            $this->container->afterResolving(AuthService::class, function ($auth, DIContainerInterface $container) {
                /**
                 * @var AuthService $auth
                 */
                $auth->setSession(
                    $container->make(SessionService::class, [['name' => 'Dave']])
                );
            });

            $auth = $this->container->make(AuthService::class);
            $this->assertEquals('Dave', $auth->getName());

            $this->container->clear();
        }

        public function testResolvingAndAfter()
        {
            // Обновляем сессию при создании
            $this->container->resolving(AuthService::class, function ($auth, DIContainerInterface $container) {
                /**
                 * @var AuthService $auth
                 */
                $auth->setSession(
                    $container->make(SessionService::class, [['name' => 'Dave']])
                );
            });
            // Обновляем сессию после создания если есть сессия с именем Dave
            $this->container->afterResolving(AuthService::class, function ($auth, DIContainerInterface $container) {
                /**
                 * @var AuthService $auth
                 */
                if ($auth->getName() === 'Dave') {
                    $auth->setSession(
                        $container->make(SessionService::class, [['name' => 'Mike']])
                    );
                }
            });

            $auth = $this->container->make(AuthService::class);

            $this->assertEquals('Mike', $auth->getName());

            $this->container->clear();
        }

        /**
         * @uses \Symbiotic\Container\Container::extend()
         */
        public function testExtend()
        {
            $this->container->extend(SessionService::class, function ($service, DIContainerInterface $container) {
                return new SessionServiceDecorator($service);
            });

            $service = $this->container->make(SessionService::class);

            $this->assertInstanceOf(SessionServiceDecorator::class, $service);

            $this->container->clear();
        }


        public function testInstance()
        {
            SingletonService::clearCountInstances();
            // test 1
            $this->container->instance(
                SingletonService::class,
                new SingletonService($this->container, 'test_value')
            );
            /**
             * @var  SingletonService $service1
             * @var  SingletonService $service2
             */
            $service1 = $this->container->make(SingletonService::class);
            $service2 = $this->container->make(SingletonService::class);

            $this->assertEquals(1, $service1->getCountInstances());
            $this->assertEquals(1, $service2->getCountInstances());

            // test 2
            $this->container->instance('db.name', 'test_db');

            $this->assertEquals('test_db', $this->container->make('db.name'));

            $this->container->clear();
        }

        public function testSingleton()
        {
            // Test with classname
            $this->container->singleton(AuthService::class, AuthService::class);
            /**
             * @var  AuthService $service1
             * @var  AuthService $service2
             */
            $service1 = $this->container->make(AuthService::class);
            $service1->setSession(new SessionService(['name' => 'Single']));

            $service2 = $this->container->make(AuthService::class);
            $this->assertEquals('Single', $service2->getName());


            // test with Closure
            SingletonService::clearCountInstances();

            $this->container->singleton(SingletonService::class, function (DIContainerInterface $container) {
                return new SingletonService($container, 'test_value');
            });
            /**
             * @var  SingletonService $service1
             * @var  SingletonService $service2
             */
            $service1 = $this->container->make(SingletonService::class);
            $service2 = $this->container->make(SingletonService::class);

            $this->assertEquals(1, $service1->getCountInstances());
            $this->assertEquals(1, $service2->getCountInstances());

            $this->container->clear();
        }


        public function testSingletonWithAlias()
        {
            // Test with classname
            $this->container->singleton(AuthService::class, AuthService::class, 'auth_service');
            /**
             * @var  AuthService $service1
             * @var  AuthService $service2
             */
            $service1 = $this->container->make(AuthService::class);


            $service2 = $this->container->make('auth_service');
            $this->assertEquals($service1, $service2);


            // test with Closure
            SingletonService::clearCountInstances();
        }


        public function testArrayAccess()
        {
            $this->container->instance('db.name', 'testdb');
            $this->assertEquals('testdb', $this->container['db.name']);

            $this->container['db.pass'] = 'password';

            $this->assertEquals('password', $this->container['db.pass']);

            $this->assertTrue(isset($this->container['db.pass']));

            unset($this->container['db.pass']);

            $this->assertFalse(isset($this->container['db.pass']));

            $this->expectException(\Exception::class);
            $data = $this->container['db.pass'];

            $this->container->clear();
        }


        public function testCallFunction()
        {
            // Тест функции без параметров
            $this->assertEquals('testFunction', $this->container->call('testFunction'));

            // Тест функции с дефолтным значением
            $this->assertEquals(
                'testFunctionWithDefaultStringParam',
                $this->container->call('testFunctionWithDefaultStringParam')
            );


            /**
             * Тест функции с парамерами по порядку(номеру)
             * @uses testFunctionWithStringParams()
             * @todo : удалено в версии 1.4.0
             */
            /* $this->assertEquals(
                 'param1param2',
                 $this->container->call('testFunctionWithStringParams', ['param1', 'param2'])
             );*/


            /**
             *  Тест функции с парамерами по именам, с переменой мест
             */
            $this->assertEquals(
                'param1param2',
                $this->container->call(
                    'testFunctionWithStringParams',
                    ['param23' => 'param23', 'param2' => 'param2', 'param1' => 'param1']
                )
            );

            // Тест функции с передачей параметра объекта
            $instance = new TestClass();
            $instance->test = 126;
            $result = $this->container->call('testFunctionWithClassParam', ['param1' => $instance]);
            $this->assertEquals(126, $result->test);

            // Тест функции с созданием объекта через рефлексию
            $result = $this->container->call('testFunctionWithClassParam');
            $this->assertInstanceOf(TestClass::class, $result);

            // Тест функции с созданием объекта через рефлексию
            $result = $this->container->call('testFunctionWithDefaultClassParam', [null]);
            // TODO: может нет нужды создавать объект если передан параметр null????!!!!
            //$this->assertEquals(null, $result);
            $this->assertInstanceOf(TestClass::class, $result);
        }


        public function testCallStaticMethod()
        {
            // Метод без параметров
            $this->assertInstanceOf(
                TestClass::class,
                $this->container->call([
                                           TestClass::class,
                                           'testStaticMethod'
                                       ])
            );

            // Тест с передачей параметров
            $this->assertEquals(
                'param1param2',
                $this->container->call([
                                           TestClass::class,
                                           'testStaticWithParams'
                                       ],
                                       ['param23' => 'param23', 'param2' => 'param2', 'param1' => 'param1'])
            );
        }

        public function testCallMethod()
        {
            /**
             * @var TestClass $instance
             */
            $instance = $this->container->make(TestClass::class);

            // добавление параметра автоматически
            $data = $this->container->call([$instance, 'testWithDefaultParam']);
            $this->assertInstanceOf(TestClass::class, $data);
        }


    }
}



