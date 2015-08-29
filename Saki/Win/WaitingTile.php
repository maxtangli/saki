<?php
namespace Saki\Win;

class WaitingTile {
    private $waitingTile;
    private $winState;
    private $remainAmount;

    function __construct($waitingTile, $winState, $remainAmount) {
        $this->waitingTile = $waitingTile;
        $this->winState = $winState;
        $this->remainAmount = $remainAmount;
    }

    function __toString() {
        return sprintf("%s, %s, %s", $this->getWaitingTile(), $this->getWinState(), $this->getRemainAmount());
    }

    function getWaitingTile() {
        return $this->waitingTile;
    }

    function getWinState() {
        return $this->winState;
    }

    function getRemainAmount() {
        return $this->remainAmount;
    }
}