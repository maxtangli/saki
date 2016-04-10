<?php

namespace Saki\Game;

class GameTurn {
    private $roundWind;
    private $dealerWind;
    private $selfWindTurn; // todo rename

    function __construct() {
        $this->roundWind = RoundWind::createEast();
        $this->dealerWind = PlayerWind::createEast();
        $this->selfWindTurn = 0;
    }

    /**
     * @return RoundWind
     */
    function getRoundWind() {
        return $this->roundWind;
    }

    /**
     * @param RoundWind $roundWind
     * @return $this
     */
    function setRoundWind(RoundWind $roundWind) {
        $this->roundWind = $roundWind;
        return $this;
    }

    /**
     * @return PlayerWind
     */
    function getDealerWind() {
        return $this->dealerWind;
    }

    /**
     * @return int
     */
    function getSelfWindTurn() {
        return $this->selfWindTurn;
    }
}