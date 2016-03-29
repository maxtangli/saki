<?php

namespace SingletonTest;

class SingletonMockClass1 extends \Saki\Util\Singleton {
}

class SingletonMockClass2 extends SingletonMockClass1 {
}

class SingletonMockClass3 extends SingletonMockClass2 {
}

class SingletonTest extends \PHPUnit_Framework_TestCase {
    function testInheritance() {
        $s1 = SingletonMockClass1::getInstance();
        $s2 = SingletonMockClass2::getInstance();
        $this->assertInstanceOf('SingletonTest\SingletonMockClass1', $s1);
        $this->assertInstanceOf('SingletonTest\SingletonMockClass2', $s2);
        $this->assertNotEquals($s1, $s2);
    }

    function testIdentity() {
        $s1 = SingletonMockClass1::getInstance();
        $s1Another = SingletonMockClass1::getInstance();
        $this->assertSame($s1, $s1Another);
    }

    function testUnsafeInheritance() {
        $s3 = SingletonMockClass3::getInstance();
        $this->assertInstanceOf('SingletonTest\SingletonMockClass3', $s3);
    }
}