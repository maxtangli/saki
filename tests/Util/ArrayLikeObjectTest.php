<?php

namespace ArrayLikeObjectTest;

use Saki\Util\ArrayLikeObject;

class ArrayLikeObjectMock extends ArrayLikeObject{
    public function setInnerArray($innerArray) {
        parent::setInnerArray($innerArray);
    }

    public function insert($item, $pos = null) {
        parent::insert($item, $pos);
    }


}

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

    function testInsert() {
        $origin = [2,3,4];
        $w = new ArrayLikeObjectMock($origin);
        $w->insert(5);
        $this->assertSame([2,3,4,5], $w->toArray());
        $w->insert(0,0);
        $this->assertSame([0,2,3,4,5], $w->toArray());
        $w->insert(1,1);
        $this->assertSame([0,1,2,3,4,5], $w->toArray());
    }
}
