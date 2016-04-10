<?php

use Saki\Game\PlayerWind;

class PlayerWindTest extends PHPUnit_Framework_TestCase {
    /**
     * @param PlayerWind $expected
     * @param PlayerWind $current
     * @param PlayerWind $nextDealer
     * @dataProvider provideToNextSelf
     */
    function testToNextSelf(PlayerWind $expected, PlayerWind $current, PlayerWind $nextDealer) {
        $actual = $current->toNextSelf($nextDealer);
        $this->assertEquals($expected, $actual,
            sprintf(
                'PlayerWind $expected[%s], PlayerWind $current[%s], PlayerWind $nextDealer[%s], $actual[%s].'
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
            PlayerWind::createEast(), PlayerWind::createSouth(), PlayerWind::createWest(), PlayerWind::createNorth()
        ];
        return [
            [$e, $e, $e],
            [$n, $e, $s],
            [$w, $e, $w],
            [$s, $e, $n],
        ];
    }

    function testToNext() {
        $this->assertEquals(PlayerWind::createWest(), PlayerWind::createEast()->toNext(2));
    }
}