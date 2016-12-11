<?php

use Saki\Game\Relation;
use Saki\Game\SeatWind;

class RelationTest extends \SakiTestCase {
    /**
     * @param string $expected
     * @param SeatWind $seatWind
     * @param SeatWind $viewer
     * @dataProvider provideCreateByOther
     */
    function testCreateByOther(string $expected, SeatWind $seatWind, SeatWind $viewer) {
        $actual = Relation::createByOther($seatWind, $viewer)->__toString();
        $this->assertEquals($expected, $actual);
    }

    function provideCreateByOther() {
        /**  viewer
         *   E S W N
         * E self prev towa next
         * S next self prev towa
         * W towa next self prev
         * N prev towa next self
         */
        list($e, $s, $w, $n) = SeatWind::createList(4)->toArray();
        return [
            ['self', $e, $e],
            ['prev', $e, $s],
            ['towards', $e, $w],
            ['next', $e, $n],
            ['next', $s, $e],
            ['towards', $w, $e],
            ['prev', $n, $e],
        ];
    }

    /**
     * @dataProvider provideToOther
     */
    function testToOther(string $expectedOther, string $relation, string $self) {
        $relation = Relation::fromString($relation);
        $actual = $relation->toOther(SeatWind::fromString($self));
        $this->assertEquals(SeatWind::fromString($expectedOther), $actual);
    }

    function provideToOther() {
        return [
            ['E', 'self', 'E'],
            ['S', 'next', 'E'],
            ['W', 'towards', 'E'],
            ['N', 'prev', 'E'],
        ];
    }
}