<?php

namespace Symbiotic\Tests\Micro\Routing;

use Symbiotic\Routing\RouterInterface;
use Symbiotic\Routing\Router;
use PHPUnit\Framework\TestCase as UnitTestCase;

/**
 * @covers \Symbiotic\Routing\Router
 * @covers \Symbiotic\Routing\Route
 */
class RoutingTest extends UnitTestCase
{
    /**
     * @var null |Router
     */
    protected Router|null $router = null;


    protected function setUp() : void
    {
        $this->router = new Router();

        $this->loadRoutes();

    }
    protected function generateTestRoutes(\Symbiotic\Routing\RouterInterface $router, $name, $module = null)
    {
        for($i = 1; $i < 10; $i++) {
            $route_uri = $name.$i;
            $router->get($route_uri, $this->prepareTestRouteParams([
                'uses' => $route_uri,
                'as' => $route_uri,
            ]));
        }
    }

    protected function loadRoutes()
    {
        /**
         * @var \Symbiotic\Routing\RouterInterface $router
         */
        $router = $this->router;
        // base routes /test(n+1)
        $this->generateTestRoutes($router,'test');


        //  group base  /test_group(n+1)
        $router->group([], function(\Symbiotic\Routing\RouterInterface $router) {
            $this->generateTestRoutes($router,'test_group');
        });

        //  group with params
        $router->group([
            'as' => 'group_prefix',
            'prefix' => 'group_prefix',
            'namespace' => 'Prefix',
            'app' => 'prefix_module',
            'middleware' => ['middleware1']
        ], function(\Symbiotic\Routing\RouterInterface $router) {
            $this->generateTestRoutes($router,'test_group');
            $router->group([
                'as' => 'subgroup',
                'prefix' => 'subgroup',
                'namespace' => 'Subgroup\\',
                'app' => 'subgroup',
                'middleware' => ['middleware1', 'middleware2']
            ], function(RouterInterface $router) {
                $this->generateTestRoutes($router,'test_subgroup');
            });
            $router->group([
                'as' => 'subgroup_base_namespace',
                'prefix' => 'subgroup_base_namespace',
                'namespace' => '\\Subgroup\\',
                'app' =>'subgroup_base_namespace'
            ], function(RouterInterface $router) {
                $this->generateTestRoutes($router,'test_subgroup');
            });
        });

    }

    public function testRouteBase()
    {

        $test1 =  $this->router->match('GET','/test4');
        $test1_head =  $this->router->match('HEAD','/test4');
        if($test1 instanceof \Symbiotic\Routing\RouteInterface) {
            $params = [
                'path' => 'test4',
                'as' => 'test4',
                'uses' =>  'test4'
            ];
            $this->assertRoute($test1, $this->prepareTestRouteParams($params));
        }
        // compare route for head method
        $this->assertEquals($test1_head, $test1);
    }

    public function testPattern()
    {
        $this->router->get('pattern/{number:\d+}', [
            'uses' => 'number',
            'as' => 'number',
        ]);

        $this->router->get('pattern/{word:[a-zA-Z]+}', [
            'uses' => 'word',
            'as' => 'word',
        ]);
        $this->router->get('pattern/{word:[a-zA-Z]+}/{string}', [
            'uses' => 'wordstring',
            'as' => 'wordstring',
        ]);

        $test1 = $this->router->match('GET','/pattern/65776dfg');
        $this->assertNull($test1);



        $test1 =  $this->router->match('GET','/pattern/45');
        $this->assertInstanceOf(\Symbiotic\Routing\RouteInterface::class,$test1);
        $params = [
            'path' => 'pattern/{number:\d+}',
            'uses' => 'number',
            'as' => 'number',
        ];
        $this->assertRoute($test1, $this->fillRouteTestParams($params));


        $test1 =  $this->router->match('GET','/pattern/test');
        $this->assertInstanceOf(\Symbiotic\Routing\RouteInterface::class,$test1);

        $params = [
            'path' => 'pattern/{word:[a-zA-Z]+}',
            'uses' => 'word',
            'as' => 'word',
        ];
        $this->assertRoute($test1, $this->fillRouteTestParams($params));

        $test1 =  $this->router->match('GET','/pattern/test/str');
        $this->assertInstanceOf(\Symbiotic\Routing\RouteInterface::class,$test1);

        $params = [
            'path' => 'pattern/{word:[a-zA-Z]+}/{string}',
            'uses' => 'wordstring',
            'as' => 'wordstring',
        ];
        $this->assertRoute($test1, $this->fillRouteTestParams($params));


    }


    public function testBaseGroup()
    {

        $test1 =  $this->router->match('GET','/test_group4');

        if($test1 instanceof \Symbiotic\Routing\RouteInterface) {
            $params = [
                'path' => 'test_group4',
                'as' => 'test_group4',
                'uses' =>  'test_group4'
            ];
            $this->assertRoute($test1, $this->prepareTestRouteParams($params));
        }
    }

    public function testGroupWithParams()
    {

        $test1 =  $this->router->match('GET','/group_prefix/test_group4');

        if($test1 instanceof \Symbiotic\Routing\RouteInterface) {
            $params = [
                'path' => 'group_prefix/test_group4',
                'as' => 'group_prefix.name_test_group4',
                'uses' =>  '\Prefix\Controller@test_group4',
                'app' =>  'prefix_module'
            ];
            $this->assertRoute($test1, $this->fillRouteTestParams($params));
        }
    }

    public function testSubGroupWithParams()
    {
        $test1 =  $this->router->match('GET','/group_prefix/subgroup/test_subgroup4');

        if($test1 instanceof \Symbiotic\Routing\RouteInterface) {
            $params = [
                'path' => 'group_prefix/subgroup/test_subgroup4',
                'as' => 'group_prefix.subgroup.name_test_subgroup4',
                'uses' =>  '\Prefix\Subgroup\Controller@test_subgroup4',
                'app' =>  'subgroup'
            ];
            $this->assertRoute($test1, $this->fillRouteTestParams($params));
        }
        $test2 =  $this->router->match('GET','/group_prefix/subgroup_base_namespace/test_subgroup4');

        if($test2 instanceof \Symbiotic\Routing\RouteInterface) {
            $params = [
                'path' => 'group_prefix/subgroup_base_namespace/test_subgroup4',
                'as' => 'group_prefix.subgroup_base_namespace.name_test_subgroup4',
                'uses' =>  '\Subgroup\Controller@test_subgroup4',
                'app' =>  'subgroup_base_namespace'
            ];
            $this->assertRoute($test2, $this->fillRouteTestParams($params));
        }
    }



    protected function prepareTestRouteParams($params) {
        foreach ([
                     'path',
                     'as',
                     'uses',
                     'app',
                 ] as $v) {
            if(!array_key_exists($v, $params)) {
                $params[$v] = null;
            } elseif($v == 'uses') {
                $params[$v] = 'Controller@'. $params[$v];
            } elseif($v == 'as') {
                $params[$v] = 'name_'. $params[$v];
            }
        }

        return $params;
    }

    protected function fillRouteTestParams($params)
    {
        foreach ([
                     'path',
                     'as',
                     'uses',
                     'app',
                 ] as $v) {
            if(!array_key_exists($v, $params)) {
                $params[$v] = null;
            }
        }
        return $params;
    }
    protected function assertRoute(\Symbiotic\Routing\RouteInterface $route, $params)
    {
        $action = $route->getAction();
        $this->assertEquals($params['path'], $route->getPath());
        $this->assertEquals($params['as'],   $route->getName());
        $this->assertEquals($params['uses'], $route->getHandler());
        $this->assertEquals($params['app'], $action['app'] ?? null);
    }

}
