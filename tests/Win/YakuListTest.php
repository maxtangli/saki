<?php

namespace YakuListTest;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\YakuList;

class MockYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 2;
    }

    protected function getNotConcealedFanCount() {
        return 1;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return true;
    }
}

class MockYaku2 extends Yaku {
    protected function getConcealedFanCount() {
        return 4;
    }

    protected function getNotConcealedFanCount() {
        return 3;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return true;
    }

    function getExcludedYakus() {
        return [MockYaku::getInstance()];
    }
}

class MockYakuMan extends Yaku {
    protected function getConcealedFanCount() {
        return 13;
    }

    protected function getNotConcealedFanCount() {
        return 13;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return true;
    }
}

class YakuListTest extends \PHPUnit_Framework_TestCase {
    function testEmpty() {
        $l = new YakuList([], true);
        $this->assertSame(0, $l->getFanCount());
        $l->normalize();
        $this->assertEquals(0, $l->count());
    }

    function testConcealed() {
        $l = new YakuList([MockYaku::getInstance(), MockYaku2::getInstance()], true);
        $this->assertEquals(2 + 4, $l->getFanCount());
        $l = new YakuList([MockYaku::getInstance(), MockYaku2::getInstance()], false);
        $this->assertEquals(1 + 3, $l->getFanCount());
    }

    function testExcluded() {
        $l = new YakuList([MockYaku::getInstance(), MockYaku2::getInstance()], true);
        $l->normalize();
        $this->assertCount(1, $l);
        $this->assertEquals(MockYaku2::getInstance(), $l->getFirst());
    }

    function testYakumanExcluded() {
        $l = new YakuList([MockYaku::getInstance(), MockYakuMan::getInstance()], true);
        $l->normalize();
        $this->assertCount(1, $l);
        $this->assertEquals(MockYakuMan::getInstance(), $l->getFirst());
    }
}