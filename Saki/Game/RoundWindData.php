<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class RoundWindData {
    private $playerCount;
    /**
     * @var TotalRoundType
     */
    private $totalRoundType;

    /**
     * @var Tile
     */
    private $roundWind; // [東] 1 局
    private $roundWindTurn; // 東 [1] 局
    private $selfWindTurn; // [0] 本場

    function __construct($playerCount, TotalRoundType $gameLengthType) {
        $this->playerCount = $playerCount;
        $this->totalRoundType = $gameLengthType;

        $this->roundWind = Tile::fromString('E');
        $this->roundWindTurn = 1;
        $this->selfWindTurn = 0;
    }

    function reset($keepDealer) {
        if ($keepDealer) {
            $this->selfWindTurn += 1;
        } else {
            if ($this->isCurrentRoundWindLastTurn()) {
                $this->roundWind = $this->roundWind->toNextTile();
                $this->roundWindTurn = 1;
            } else {
                $this->roundWindTurn += 1;
            }
            $this->selfWindTurn = 0;
        }
    }

    function getPlayerCount() {
        return $this->playerCount;
    }

    function getTotalRoundType() {
        return $this->totalRoundType;
    }

    function getRoundWind() {
        return $this->roundWind;
    }

    function setRoundWind(Tile $roundWind) {
        $this->roundWind = $roundWind;
    }

    function isRoundWind(Tile $tile) {
        return $tile == $this->getRoundWind();
    }

    function isSelfWind(Player $player, Tile $tile) {
        return $player->isSelfWind($tile);
    }

    function isDoubleWind(Player $player, Tile $tile) {
        return $this->isRoundWind($tile) && $this->isSelfWind($player, $tile);
    }

    function getRoundWindTurn() {
        return $this->roundWindTurn;
    }

    function setRoundWindTurn($roundWindTurn) {
        $this->roundWindTurn = $roundWindTurn;
    }

    function getSelfWindTurn() {
        return $this->selfWindTurn;
    }

    function setSelfWindTurn($selfWindTurn) {
        $this->selfWindTurn = $selfWindTurn;
    }

    function isCurrentRoundWindLastTurn() {
        return $this->getRoundWindTurn() == $this->getPlayerCount();
    }

    // 游戏长度定下的最后一局,分不出胜负时进入延长局。
    function isLastOrExtraRound() {
        $isLastRoundWind = $this->getRoundWind() == $this->getTotalRoundType()->getLastRoundWind();
        $isLastRound = $isLastRoundWind && $this->isCurrentRoundWindLastTurn();
        return $isLastRound || $this->isExtraRound();
    }

    // 延长局？
    function isExtraRound() {
        return !$this->getTotalRoundType()->isInLengthRoundWind($this->getRoundWind());
    }

    // 最终局，完局后游戏结束
    function isFinalRound() {
        // 最多延长一个场风
        if ($this->getTotalRoundType()->getValue() == TotalRoundType::FULL) {
            throw new \LogicException('un implemented.');
        }
        $finalRoundWind = $this->getTotalRoundType()->getLastRoundWind()->toNextTile();
        $isFinalRoundWind = $this->getRoundWind() == $finalRoundWind;
        return $isFinalRoundWind && $this->isCurrentRoundWindLastTurn();
    }
}