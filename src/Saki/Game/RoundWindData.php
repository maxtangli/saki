<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class RoundWindData {
    private $playerCount;
    /**
     * @var GameLengthType
     */
    private $gameLengthType;

    /**
     * @var Tile
     */
    private $roundWind; // [東] 1 局

    private $roundWindTurn; // 東 [1] 局
    private $selfWindTurn; // [0] 本場

    function __construct($playerCount, GameLengthType $gameLengthType) {
        $this->playerCount = $playerCount;
        $this->gameLengthType = $gameLengthType;

        $this->roundWind = Tile::fromString('E');
        $this->roundWindTurn = 1;
        $this->selfWindTurn = 0;
    }

    function reset($keepDealer) {
        if ($keepDealer) {
            // keep roundWind
            // keep roundWindTurn
            $this->selfWindTurn += 1;
        } else { // not keep dealer
            if ($this->isCurrentRoundWindLastTurn()) {
                $this->roundWind = $this->roundWind->getNextTile();
                $this->roundWindTurn = 1;
                $this->selfWindTurn = 0;
            } else {
                // keep roundWind
                $this->roundWindTurn += 1;
                $this->selfWindTurn = 0;
            }
        }
    }

    function debugReset(Tile $roundWind = null, $roundWindTurn = null, $selfWindTurn = null) {
        if ($roundWind && !$roundWind->isWind()) {
            throw new \InvalidArgumentException();
        }

        $this->roundWind = $roundWind ?? Tile::fromString('E');
        $this->roundWindTurn = $roundWindTurn ?? 1;
        $this->selfWindTurn = $selfWindTurn ?? 0;
    }

    function getPlayerCount() {
        return $this->playerCount;
    }

    function getTotalRoundType() {
        return $this->gameLengthType;
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
        if ($this->getTotalRoundType()->getValue() == GameLengthType::FULL) {
            throw new \LogicException('un implemented.');
        }
        $finalRoundWind = $this->getTotalRoundType()->getLastRoundWind()->getNextTile();
        $isFinalRoundWind = $this->getRoundWind() == $finalRoundWind;
        return $isFinalRoundWind && $this->isCurrentRoundWindLastTurn();
    }
}