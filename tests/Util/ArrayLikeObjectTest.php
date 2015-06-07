<?php

namespace ArrayReadonlyWrapperTest;

use Saki\Util\ArrayLikeObject;

class ArrayLikeObjectTest extends \PHPUnit_Framework_TestCase {
    function testReferenceModify() {
        $origin = [2,3,4];
        $expected = [1,2,3];
        $w = new ArrayLikeObject($origin);
        foreach($w as $k => &$v) {
            $v -= 1;
        }
        for ($i = 0; $i < count($origin); ++$i) {
            $this->assertSame($origin[$i], $w[$i]);
        }
    }
}
