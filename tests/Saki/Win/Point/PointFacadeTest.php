<?php

use Saki\Game\SeatWind;
use Saki\Win\Point\PointList;

class PointListTest extends SakiTestCase {
    function testFromPairs() {
        $f = PointList::fromPointPairs([
            [SeatWind::createNorth(), 22200],
            [SeatWind::createSouth(), 22200],
            [SeatWind::createWest(), 22300],
            [SeatWind::createEast(), 31100],
        ])->toOrderByRank();
        $this->assertPointList([
            ['E', 31100, 1],
            ['W', 22300, 2],
            ['S', 22200, 3],
            ['N', 22200, 4],
        ], $f);
        $this->assertPointItem('E', 31100, 1, $f->getSingleTop());
    }
}
