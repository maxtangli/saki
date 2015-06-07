<?php


namespace tests\php;

class EqMockA {

}

class EqMockB extends EqMockA{

}

class CalledClassMockA {
    public $calledClass;
    function __construct() {
        $this->calledClass = get_called_class();
    }
}

class CalledClassMockB extends CalledClassMockA {

}

class PassArgumentArrayMock {
    public $a = [1,2,3];
    function getA() {
        return $this->a;
    }
}

class PhpTest extends \PHPUnit_Framework_TestCase{
    function testOperatorEq() {
        $a = new EqMockA();
        $a2 = new EqMockA();
        $this->assertEquals($a, $a2);
        $this->assertNotSame($a, $a2);
        $b = new EqMockB();
        $this->assertNotEquals($a, $b);
    }

    function testCalledClass() {
        $a = new CalledClassMockA();
        $this->assertEquals('tests\php\CalledClassMockA',$a->calledClass);
        $b = new CalledClassMockB();
        $this->assertEquals('tests\php\CalledClassMockB',$b->calledClass);
    }

    function testBool() {
        if ([]) {
            $a = true;
        } else {
            $a = false;
        }
        $this->assertFalse($a);
    }

    function testPassArgumentArray() {
        $obj = new PassArgumentArrayMock();
        $getA = $obj->getA();
        $getA[0] = 0;
        $this->assertEquals([0,2,3], $getA);
        $this->assertEquals([1,2,3], $obj->getA()); // array will pass by value
    }

    function testArraySplice() {
        $a = [1,0,1,2,3];
        array_splice($a, 0, 1);
        $this->assertEquals([0,1,2,3], $a); // number key rearrange after splice
    }

    function testExplode() {
        $a = explode(',', '');
        $this->assertSame([''], $a);

        $a = explode(',', 'abc');
        $this->assertSame(['abc'], $a);

    }
}