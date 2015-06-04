<?php

class SingletonMockClass1 extends \Saki\Util\Singleton {

}

class SingletonMockClass2 extends SingletonMockClass1 {

}

class SingletonTest extends PHPUnit_Framework_TestCase {
    function testInheritance() {
        $s0 = \Saki\Util\Singleton::getInstance();
        $s1 = SingletonMockClass1::getInstance();
        $s2 = SingletonMockClass2::getInstance();
        $this->assertInstanceOf('SingletonMockClass1', $s1);
        $this->assertInstanceOf('SingletonMockClass2', $s2);
        $this->assertNotEquals($s0, $s1);
        $this->assertNotEquals($s1, $s2);
    }
}
