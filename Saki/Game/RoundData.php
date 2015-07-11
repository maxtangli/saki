<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class RoundData {
    // e.x. [東] [1] 局 [0] 本場

    /**
     * @var Tile
     */
    private $roundWind;
    private $roundWindTurn;
    private $selfWindTurn;

    /**
     * @var Wall
     */
    private $wall; // 牌山

    private $accumulatedReachCount; // 場棒

    function __construct() {
        $this->accumulatedReachCount = 0;
        $this->wall = new Wall(TileSet::getStandardTileSet());
        $this->init();
    }

    function init() {
        $this->reset(Tile::fromString('E'), 1, 0);
    }

    function toNextRound($dealerChanged, $roundChanged) {
        $keepSelfWind = !$dealerChanged;
        $keepRoundWind = !$roundChanged;
        if ($keepSelfWind) {
            if (!$keepRoundWind) {
                throw new \InvalidArgumentException();
            }
            $this->reset($this->getRoundWind(), $this->getRoundWindTurn(), $this->getSelfWindTurn() + 1);
        } else {
            $roundWind = $keepRoundWind ? $this->getRoundWind() : $this->getRoundWind()->toNextTile();
            $roundWindTurn = $keepRoundWind ? $this->getRoundWindTurn() : 1;
            $selfWindTurn = $this->getSelfWindTurn() + 1;
            $this->reset($roundWind, $roundWindTurn, $selfWindTurn);
        }
    }

    protected function reset(Tile $roundWind, $roundWindTurn, $selfWindTurn) {
        $this->getWall()->reset(true);
        $this->roundWind = $roundWind;
        $this->roundWindTurn = $roundWindTurn;
        $this->selfWindTurn = $selfWindTurn;
    }

    function getWall() {
        return $this->wall;
    }

    function setWall($wall) {
        $this->wall = $wall;
    }

    function getRoundWind() {
        return $this->roundWind;
    }

    function setRoundWind(Tile $roundWind) {
        $this->roundWind = $roundWind;
    }

    function getRoundWindTurn() {
        return $this->roundWindTurn;
    }

    function setRoundWindTurn($roundWindTurn) {
        $this->roundWindTurn = $roundWindTurn;
    }

    function isLastRoundWindTurn() {
        $playerCount = 4; // todo
        return $this->roundWindTurn == $playerCount;
    }

    function getSelfWindTurn() {
        return $this->selfWindTurn;
    }

    function setSelfWindTurn($selfWindTurn) {
        $this->selfWindTurn = $selfWindTurn;
    }

    function getAccumulatedReachCount() {
        return $this->accumulatedReachCount;
    }

    function setAccumulatedReachCount($accumulatedReachCount) {
        $this->accumulatedReachCount = $accumulatedReachCount;
    }

    function getAccumulatedReachScore() {
        return $this->getAccumulatedReachCount() * 1000;
    }

    function addAccumulatedReachCount() {
        $this->setAccumulatedReachCount($this->getAccumulatedReachCount() + 1);
    }
}