<?php

class EnumMockClass1 extends \Saki\Util\Enum {
    static function getValue2StringMap() {
        return [1 => 'EnumMockClass1', 2 => 'EnumMockClass1.2'];
    }

    static function getClassName() {
        return __CLASS__;
    }
}

class EnumMockClass2 extends EnumMockClass1 {
    static function getValue2StringMap() {
        return [1 => 'EnumMockClass2'];
    }

    static function getClassName() {
        return __CLASS__;
    }
}

class EnumMockClass3 extends \Saki\Util\Enum {
    const FILED_1 = 1;
    const FIELD_2 = 2;
}

class EnumTest extends \SakiTestCase {
    function testInheritance() {
        $s1 = EnumMockClass1::create(1);
        $s2 = EnumMockClass2::create(1);
        $this->assertInstanceOf('EnumMockClass1', $s1);
        $this->assertInstanceOf('EnumMockClass2', $s2);
        $this->assertNotEquals($s1, $s2);
    }

    function testIdentity() {
        $s1 = EnumMockClass1::create(1);
        $s1Another = EnumMockClass1::create(1);
        $s2 = EnumMockClass1::create(2);
        $this->assertSame($s1, $s1Another);
        $this->assertNotSame($s1, $s2);
    }

    function testAutoConst() {
        $e = EnumMockClass3::create(EnumMockClass3::FIELD_2);
        $this->assertInstanceOf('EnumMockClass3', $e);
        $this->assertEquals($e->getValue(), EnumMockClass3::FIELD_2);
    }

    function testCreateAll() {
        $all = EnumMockClass3::createAll();
        $expected = [
            EnumMockClass3::create(EnumMockClass3::FILED_1),
            EnumMockClass3::create(EnumMockClass3::FIELD_2)
        ];
        $this->assertEquals($expected, $all);
    }
}
