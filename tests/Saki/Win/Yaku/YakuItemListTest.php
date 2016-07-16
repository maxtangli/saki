<?php

namespace YakuListTest;

use Saki\Win\Yaku\YakuItemList;

class YakuItemListTest extends \SakiTestCase {
    function testEmpty() {
        $l = new YakuItemList();
        $this->assertSame(0, $l->getTotalFan());
        $l->normalize();
        $this->assertEquals(0, $l->count());
    }
    
    function testConcealed() {
        // todo
    }

    function testExcluded() {
        // todo
    }

    function testYakumanExcluded() {
        // todo
    }
}