<?php

use Saki\Game\PointItem;
use Saki\Game\SeatWind;
use Saki\Win\Point\PointList;

class PointListTest extends \SakiTestCase {
    function assertPointItem(string $seatWindString, int $point, int $rank, PointItem $pointItem) {
        $this->assertEquals(SeatWind::fromString($seatWindString), $pointItem->getSeatWind());
        $this->assertEquals($point, $pointItem->getPoint());
        $this->assertEquals($rank, $pointItem->getRank());
    }

    function assertPointList(array $expectItems, PointList $pointList) {
        foreach ($expectItems as $i => list($seatWindString, $point, $rank)) {
            $pointItem = $pointList[$i];
            $this->assertPointItem($seatWindString, $point, $rank, $pointItem);
        }
    }
    
    function testFromPairs() {
        $f = PointList::fromPointMap([
            'N' => 22200,
            'S' => 22200,
            'W' => 22300,
            'E' => 31100,
        ])->toOrderByRank();
        $this->assertPointList([
            ['E', 31100, 1],
            ['W', 22300, 2],
            ['S', 22200, 3],
            ['N', 22200, 4],
        ], $f);
        $this->assertPointItem('E', 31100, 1, $f->getSingleTop());
    }

    function testPointList() {
        $round = $this->getInitRound();

        $facade = $round->getPointHolder()->getPointList();

        $this->assertFalse($facade->hasMinus());
        $this->assertTrue($facade->hasTiledTop());
        $this->assertEquals(25000, $facade->getFirst()->getPoint());
    }
}
