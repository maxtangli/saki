<?php

use Saki\Game\SeatWind;
use Saki\Win\Point\PointList;

class PointListTest extends SakiTestCase {
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
}
