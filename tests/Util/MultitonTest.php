<?php

class MultitonMockClass1 extends \Saki\Util\Multiton {

}

class MultitonMockClass2 extends MultitonMockClass1 {

}

class MultitonTest extends PHPUnit_Framework_TestCase {
    function testInheritance() {
        $s0 = \Saki\Util\Multiton::getInstance(1);
        $s1 = MultitonMockClass1::getInstance(1);
        $s2 = MultitonMockClass2::getInstance(1);
        $this->assertInstanceOf('MultitonMockClass1', $s1);
        $this->assertInstanceOf('MultitonMockClass2', $s2);
        $this->assertNotEquals($s0, $s1);
        $this->assertNotEquals($s1, $s2);
    }

    function testIdentity() {
        $s1 = MultitonMockClass1::getInstance(1);
        $s1Another = MultitonMockClass1::getInstance(1);
        $s2 = MultitonMockClass1::getInstance(2);
        $this->assertSame($s1, $s1Another);
        $this->assertNotSame($s1, $s2);
    }
}
