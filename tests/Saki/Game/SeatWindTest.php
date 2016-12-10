<?php

use Saki\Game\Relation;
use Saki\Game\SeatWind;

class SeatWindTest extends \SakiTestCase {
    /**
     * @param SeatWind $expected
     * @param SeatWind $current
     * @param SeatWind $nextDealer
     * @dataProvider provideToNextSelf
     */
    function testToNextSelf(SeatWind $expected, SeatWind $current, SeatWind $nextDealer) {
        $actual = $current->toNextSelf($nextDealer);
        $this->assertEquals($expected, $actual,
            sprintf(
                'SeatWind $expected[%s], SeatWind $current[%s], SeatWind $nextDealer[%s], $actual[%s].'
                , $expected, $current, $nextDealer, $actual
            )
        );
    }

    function provideToNextSelf() {
        /**  next
         *   E S W N
         * E E N W S
         * S
         * W
         * N
         */
        list($e, $s, $w, $n) = SeatWind::createList(4)->toArray();
        return [
            [$e, $e, $e],
            [$n, $e, $s],
            [$w, $e, $w],
            [$s, $e, $n],
        ];
    }

    function testToNext() {
        $this->assertEquals(SeatWind::createWest(), SeatWind::createEast()->toNext(2));
    }

    /**
     * @param string $expected
     * @param SeatWind $seatWind
     * @param SeatWind $viewer
     * @dataProvider provideToRelation
     */
    function testToRelation(string $expected, SeatWind $seatWind, SeatWind $viewer) {
        $actual = Relation::createByTarget($seatWind, $viewer)->__toString();
        $this->assertEquals($expected, $actual);
    }

    function provideToRelation() {
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
}