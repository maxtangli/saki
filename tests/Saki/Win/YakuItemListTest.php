<?php

namespace YakuListTest;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\YakuItemList;

class MockYaku extends Yaku {
    public function getConcealedFan() {
        return 2;
    }

    public function getNotConcealedFan() {
        return 1;
    }

    public function getRequiredTileSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return true;
    }
}

class MockYaku2 extends Yaku {
    public function getConcealedFan() {
        return 4;
    }

    public function getNotConcealedFan() {
        return 3;
    }

    public function getRequiredTileSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return true;
    }

    function getExcludedYakus() {
        return [MockYaku::create()];
    }
}

class MockYakuMan extends Yaku {
    public function getConcealedFan() {
        return 13;
    }

    public function getNotConcealedFan() {
        return 13;
    }

    public function getRequiredTileSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return true;
    }
}

class YakuItemListTest extends \PHPUnit_Framework_TestCase {
    function testEmpty() {
        $l = new YakuItemList();
        $this->assertSame(0, $l->getTotalFan());
        $l->normalize();
        $this->assertEquals(0, $l->count());
    }

//  todo refactor into right ver
//    function testConcealed() {
//        $l = new YakuItemList([MockYaku::create(), MockYaku2::create()], true);
//        $this->assertEquals(2 + 4, $l->getTotalFan());
//        $l = new YakuItemList([MockYaku::create(), MockYaku2::create()], false);
//        $this->assertEquals(1 + 3, $l->getTotalFan());
//    }
//
//    function testExcluded() {
//        $l = new YakuItemList([MockYaku::create(), MockYaku2::create()], true);
//        $l->normalize();
//        $this->assertCount(1, $l);
//        $this->assertEquals(MockYaku2::create(), $l->getFirst());
//    }
//
//    function testYakumanExcluded() {
//        $l = new YakuItemList([MockYaku::create(), MockYakuMan::create()], true);
//        $l->normalize();
//        $this->assertCount(1, $l);
//        $this->assertEquals(MockYakuMan::create(), $l->getFirst());
//    }
}