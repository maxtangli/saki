<?php
namespace Saki\Game;

use Saki\Util\Enum;

/**
 * @package Saki\Game
 */
class PlayerType extends Enum {
    const TWO = 2;
    const THREE = 3;
    const FOUR = 4;

    /**
     * @return \Saki\Util\ArrayList
     */
    function getSeatWindList() {
        return SeatWind::createList($this->getValue());
    }

    /**
     * @return array e.x. ['E' => $defaultValue, ...]
     */
    function getSeatWindMap($defaultValue) {
        $seatWindList = $this->getSeatWindList();
        $keys = $seatWindList->toArray(function (SeatWind $seatWind) {
            return $seatWind->__toString();
        });
        $values = array_fill(0, $seatWindList->count(), $defaultValue);
        return array_combine($keys, $values);
    }
}