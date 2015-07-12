<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileSet;

class RoundData {
    /**
     * @var Tile
     */
    private $roundWind; // [東] 1 局
     // 東 [1] 局
    private $selfWindTurn; // [0] 本場

    private $lastRoundWind; // [東]風戦・東[南]戦
    /**
     * @var Wall
     */
    private $wall; // 牌山
    private $accumulatedReachCount; // 積み棒
    /**
     * @var PlayerList
     */
    private $playerList;

    /**
     * default: 4 player, east game, 25000-30000 initial score,
     */
    function __construct() {
        $this->roundWind = Tile::fromString('E');
        $this->selfWindTurn = 0;
        $this->lastRoundWind = Tile::fromString('E');
        $this->wall = new Wall(TileSet::getStandardTileSet());
        $this->accumulatedReachCount = 0;
        $this->playerList = PlayerList::getStandardPlayerList();
    }

    function reset($keepDealer) {
        if (!is_bool($keepDealer)) {
            throw new \InvalidArgumentException('bool expected.');
        }
        $nextDealer = $keepDealer ? $this->getPlayerList()->getDealerPlayer() : $this->getPlayerList()->getDealerOffsetPlayer(1);
        $roundChanged = !$keepDealer && $nextDealer->getNo() == 1;
        if ($roundChanged) {
            $this->roundWind = $this->getRoundWind()->toNextTile();
        }
        $this->selfWindTurn = $keepDealer ? $this->getSelfWindTurn() + 1 : 0;
        $this->getWall()->reset(true);
        $this->getPlayerList()->reset($nextDealer);
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
        return $this->getPlayerList()->getDealerPlayer()->getNo();
    }

    function getSelfWindTurn() {
        return $this->selfWindTurn;
    }

    function setSelfWindTurn($selfWindTurn) {
        $this->selfWindTurn = $selfWindTurn;
    }

    function getLastRoundWind() {
        return $this->lastRoundWind;
    }

    function setLastRoundWind($lastRoundWind) {
        $this->lastRoundWind = $lastRoundWind;
    }

    function getAccumulatedReachCount() {
        return $this->accumulatedReachCount;
    }

    function setAccumulatedReachCount($accumulatedReachCount) {
        $this->accumulatedReachCount = $accumulatedReachCount;
    }

    function getPlayerList() {
        return $this->playerList;
    }

    function setPlayerList($playerList) {
        $this->playerList = $playerList;
    }

    function getAccumulatedSelfWindTurnScore() {
        return $this->getSelfWindTurn() * 300;
    }

    function getAccumulatedReachScore() {
        return $this->getAccumulatedReachCount() * 1000;
    }

    function addAccumulatedReachCount() {
        $this->setAccumulatedReachCount($this->getAccumulatedReachCount() + 1);
    }

    function isLastNorthRoundWindTurn() {
        $playerCount = $this->getPlayerList()->count();
        return $this->getRoundWind() == Tile::fromString('N') && $this->getRoundWindTurn() == $playerCount;
    }

    function isLastOrExtraRoundWindTurn() {
        $windNos = [
            'E' => 1, 'S' => 2, 'W' => 3, 'N' => 4,
        ];
        list($roundWindNo, $lastRoundWindNo) = [$windNos[(string)$this->getRoundWind()], $windNos[(string)$this->getLastRoundWind()]];
        $playerCount = $this->getPlayerList()->count();
        $isLastRoundWind = ($roundWindNo == $lastRoundWindNo) && $this->getRoundWindTurn() == $playerCount;
        $isExtraRoundWind = $roundWindNo > $lastRoundWindNo;
        return $isLastRoundWind || $isExtraRoundWind;
    }
}