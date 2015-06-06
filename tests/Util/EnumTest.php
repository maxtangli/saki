<?php

class EnumMockClass1 extends \Saki\Util\Enum {
    static function getValue2StringMap() {
        return [1 => 'EnumMockClass1',2 => 'EnumMockClass1.2'];
    }

    static function getClassName() {
        return __CLASS__;
    }

    /**
     * @param $value
     * @return EnumMockClass1
     */
    static function getInstance($value) {
        return parent::getInstance($value);
    }

    /**
     * @param string $s
     * @return EnumMockClass1
     */
    static function fromString($s) {
        return parent::fromString($s);
    }
}

class EnumMockClass2 extends EnumMockClass1 {
    static function getValue2StringMap() {
        return [1 => 'EnumMockClass2'];
    }

    static function getClassName() {
        return __CLASS__;
    }

    /**
     * @param $value
     * @return EnumMockClass2
     */
    static function getInstance($value) {
        return parent::getInstance($value);
    }

    /**
     * @param string $s
     * @return EnumMockClass2
     */
    static function fromString($s) {
        return parent::fromString($s);
    }
}

class EnumTest extends PHPUnit_Framework_TestCase {
    function testInheritance() {
        $s1 = EnumMockClass1::getInstance(1);
        $s2 = EnumMockClass2::getInstance(1);
        $this->assertInstanceOf('EnumMockClass1', $s1);
        $this->assertInstanceOf('EnumMockClass2', $s2);
        $this->assertNotEquals($s1, $s2);
    }

    function testIdentity() {
        $s1 = EnumMockClass1::getInstance(1);
        $s1Another = EnumMockClass1::getInstance(1);
        $s2 = EnumMockClass1::getInstance(2);
        $this->assertSame($s1, $s1Another);
        $this->assertNotSame($s1, $s2);
    }
}
