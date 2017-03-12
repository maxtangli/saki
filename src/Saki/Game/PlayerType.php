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
     * @return int
     */
    function getPublicPhaseValue() {
        return $this->getValue() - 1;
    }

    /**
     * @param callable $selector
     * @return \Saki\Util\ArrayList
     */
    function getSeatWindList(callable $selector = null) {
        return SeatWind::createList($this->getValue())
            ->toArrayList($selector);
    }

    /**
     * @param $defaultValue
     * @return array e.g. ['E' => $defaultValue, ...]
     */
    function getSeatWindMap($defaultValue) {
        $seatWindList = $this->getSeatWindList();
        $keys = $seatWindList->toArray(function (SeatWind $seatWind) {
            return $seatWind->__toString();
        });
        if (is_callable($defaultValue)) {
            $values = $seatWindList->toArray($defaultValue);
        } else {
            $values = array_fill(0, $seatWindList->count(), $defaultValue);
        }
        return array_combine($keys, $values);
    }
}