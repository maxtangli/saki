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
}