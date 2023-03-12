<?php

namespace Symbiotic\Tests\Micro\Container\Classes;


class TestClass
{

    public static function testStaticMethod(): TestClass
    {
        return new static;
    }

    public static function testStaticWithParams(string $param1, string $param2)
    {
        return $param1.$param2;
    }

    public static function testStaticWithClassDefaultParam(TestClass $param1 = null)
    {
        return $param1;
    }

    public function testWithParam(string $param1): string
    {
        return $param1;
    }

    /**
     * @param TestClass|null $param
     * @return TestClass
     */
    public function testWithDefaultParam(TestClass $param = null): ? TestClass
    {
        return $param;
    }

}