<?php

use Saki\Game\SeatWind;

class SeatWindTest extends PHPUnit_Framework_TestCase {
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
        list($e, $s, $w, $n) = [
            SeatWind::createEast(), SeatWind::createSouth(), SeatWind::createWest(), SeatWind::createNorth()
        ];
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
}