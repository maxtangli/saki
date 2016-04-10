<?php

namespace Saki\Game;

class GameTurn {
    private $prevailingWind;
    private $dealerWind;
    private $seatWindTurn; // todo rename

    function __construct() {
        $this->prevailingWind = PrevailingWind::createEast();
        $this->dealerWind = SeatWind::createEast();
        $this->seatWindTurn = 0;
    }

    /**
     * @return PrevailingWind
     */
    function getPrevailingWind() {
        return $this->prevailingWind;
    }

    /**
     * @param PrevailingWind $prevailingWind
     * @return $this
     */
    function setPrevailingWind(PrevailingWind $prevailingWind) {
        $this->prevailingWind = $prevailingWind;
        return $this;
    }

    /**
     * @return SeatWind
     */
    function getDealerWind() {
        return $this->dealerWind;
    }

    /**
     * @return int
     */
    function getSeatWindTurn() {
        return $this->seatWindTurn;
    }
}