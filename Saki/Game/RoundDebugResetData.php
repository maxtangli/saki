<?php

namespace Saki\Game;

use Saki\Tile\Tile;

class RoundDebugResetData {
    // RoundWindData
    private $roundWind;
    private $roundWindTurnData;
    private $selfWindTurn;

    /**
     * note: no params provided since
     * - it's more convenient to set RoundWindTurnData by setters setRoundWindTurn() and setDealerWind()
     */
    function __construct() {
        $this->roundWind = Tile::fromString('E');
        $this->roundWindTurnData = new RoundWindTurnData(1);
        $this->selfWindTurn = 0;
    }

    function getRoundWind() {
        return $this->roundWind;
    }

    function setRoundWind(Tile $roundWind) {
        $this->roundWind = $roundWind;
        return $this;
    }

    function getRoundWindTurn() {
        return $this->roundWindTurnData->getTurn();
    }

    function setRoundWindTurn(int $roundWindTurn) {
        $this->roundWindTurnData->setTurn($roundWindTurn);
        return $this;
    }

    function getDealerWind() {
        return $this->roundWindTurnData->getDealerWind();
    }

    function setDealerWind(Tile $wind) {
        $this->roundWindTurnData->setDealerWind($wind);
        return $this;
    }

    function getSelfWindTurn() {
        return $this->selfWindTurn;
    }

    function setSelfWindTurn(int $selfWindTurn) {
        $this->selfWindTurn = $selfWindTurn;
        return $this;
    }
}